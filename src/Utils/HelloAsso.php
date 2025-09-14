<?php

namespace App\Utils;

use App\Entity\Configuration;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Payment;
use App\Entity\PaymentOrder;
use App\Repository\ConfigurationRepository;
use App\Repository\PaymentOrderRepository;
use App\Repository\PaymentRepository;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HelloAsso
{
    private ?string $accessToken = null;

    public function __construct(protected string $helloAssoApiUrl, protected ConfigurationRepository $configuration,
        protected HttpClientInterface $client, protected RouterInterface $router, protected PaymentRepository $paymentRepository,
        protected PaymentOrderRepository $paymentOrderRepository, protected CacheInterface $cache)
    {
    }

    public function intentCheckout(Order $order, float $amount): string
    {
        $payment = (new Payment())->setMember($order->getMember())->setMethod(Payment::METHOD_HELLO_ASSO);
        $this->paymentRepository->update($payment);
        $lines = array_map(fn (OrderLine $line) => $line->getLabel(), $order->getLines()->toArray());

        /* @todo url https ? */
        $backUrl = str_replace('http:', 'https:', $this->router->generate('order_pay_with_hello_asso', ['identifier' => $order->getIdentifier()], RouterInterface::ABSOLUTE_URL));
        $body = [
            'totalAmount' => round($order->getDueAmount() * 100),
            'initialAmount' => round($order->getDueAmount() * 100),
            'itemName' => substr(implode(' | ', $lines), 0, 250),
            'backUrl' => $backUrl,
            'errorUrl' => $backUrl,
            'returnUrl' => str_replace('http:', 'https:', $this->router->generate('order_show_payment', ['identifier' => $order->getIdentifier(), 'payment' => $payment->getId()], RouterInterface::ABSOLUTE_URL)),
            'containsDonation' => false,
            'payer' => [
                'firstName' => $order->getMember()->getFirstName(),
                'lastName' => $order->getMember()->getLastName(),
                'email' => $order->getMember()->getEmail(),
                'address' => $order->getMember()->getStreets(' '),
                'city' => $order->getMember()->getCity(),
                'zipCode' => $order->getMember()->getPostalCode(),
                'country' => 'FRA',
            ],
            'metadata' => [
                'order' => $order->getIdentifier(),
                'order_lines' => $lines,
                'payment_id' => $payment->getId(),
                'member' => $order->getMember()->getFriendlyName(),
                'member_id' => $order->getMember()->getId(),
            ],
        ];

        $response = $this->client->request(
            'POST',
            "{$this->helloAssoApiUrl}/v5/organizations/{$this->configuration->getValue(Configuration::ITEM_HELLOASSO_ASSO_SLUG)}/checkout-intents",
            [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$this->getAccessToken()],
                'body' => json_encode($body),
            ]
        );

        $data = $response->toArray();
        $payment->setIssuedAt(date_create_immutable());
        $payment->setCheckoutId($data['id'] ?? throw new \RuntimeException('Unable to store checkout intent id '));
        $this->paymentRepository->update($payment);
        $this->paymentOrderRepository->update((new PaymentOrder($payment, $order))->setAmount($amount));

        return $data['redirectUrl'] ?? throw new \RuntimeException('Unable to retrieve redirect url for checkout');
    }

    public function getCheckoutStatus(Payment $payment): void
    {
        if (Payment::METHOD_HELLO_ASSO !== $payment->getMethod()) {
            return;
        }

        $response = $this->client->request(
            'GET',
            "{$this->helloAssoApiUrl}/v5/organizations/{$this->configuration->getValue(Configuration::ITEM_HELLOASSO_ASSO_SLUG)}/checkout-intents/{$payment->getCheckoutId()}",
            [
                'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'Bearer '.$this->getAccessToken()],
            ]
        );

        try {
            // @todo handle multi payments.
            $result = $response->toArray();
            $state = $result['order']['payments'][0]['state'] ?? null;
            $date = $result['order']['payments'][0]['date'] ?? date('Y-m-d H:i:s');

            if ('Authorized' === $state) {
                $payment->setStatus(Payment::STATUS_VALIDATED);
                $payment->setReceivedAt(date_create_immutable($date));
            }
            if (\in_array($state, ['Refunded', 'Refused'])) {
                $payment->setStatus(Payment::STATUS_CANCELLED);
            }
            // if after 45 minutes, you are unable to retrieve any payment via polling, then the checkout can be considered abandoned.
            if (null === $state && $payment->getIssuedAt() < date_create_immutable('-1 hour')) {
                $payment->setStatus(Payment::STATUS_CANCELLED);
            }
            $this->paymentRepository->update($payment);
        } catch (ExceptionInterface $exception) {
        }
    }

    protected function retrieveAccessToken(): string
    {
        $cacheKey = 'helloasso_accesstoken_'.$this->configuration->getValue(Configuration::ITEM_HELLOASSO_CLIENT_ID);

        return $this->cache->get($cacheKey,
            function (ItemInterface $item) {
                $response = $this->client->request('POST', $this->helloAssoApiUrl.'/oauth2/token', [
                    'headers' => [
                        'Content-Type' => 'application/x-www-form-urlencoded',
                    ],
                    'body' => [
                        'grant_type' => 'client_credentials',
                        'client_id' => $this->configuration->getValue(Configuration::ITEM_HELLOASSO_CLIENT_ID),
                        'client_secret' => $this->configuration->getValue(Configuration::ITEM_HELLOASSO_CLIENT_SECRET),
                    ],
                ]);
                $tokens = $response->toArray();
                $item->expiresAfter($tokens['expires_in'] - 1); // 24h ?!

                return $tokens['access_token'];
            }
        );
    }

    protected function getAccessToken(): string
    {
        if (null === $this->accessToken) {
            $this->accessToken = $this->retrieveAccessToken();
        }

        return $this->accessToken;
    }
}
