<?php

namespace App\Tests\Controller;

use App\Entity\Order;
use Symfony\Component\HttpFoundation\Request;

final class OrderControllerTest extends AbstractWebTestCase
{
    public function testPay()
    {
        $order = $this->em->getRepository(Order::class)->findBy([], ['id' => 'desc'], 1)[0];
        $this->client->request(Request::METHOD_GET, $this->getUrl('order_pay', ['identifier' => $order->getIdentifier()]));
        $this->assertResponseIsSuccessful();
    }
}
