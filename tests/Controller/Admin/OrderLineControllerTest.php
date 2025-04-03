<?php

namespace App\Tests\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderLine;
use App\Tests\Controller\AbstractWebTestCase;
use Symfony\Component\HttpFoundation\Request;

final class OrderLineControllerTest extends AbstractWebTestCase
{
    public function testShouldBeAuthenticated()
    {
        $order = $this->getFixtureOrder();
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getEditUrl($order->getLines()->first()));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getCreateUrl($order, OrderLine::TYPE_SIMPLE));
        $this->assertRedirectToLogin(Request::METHOD_GET, $this->getDeleteUrl($order->getLines()->first()));
    }

    public function testEdit()
    {
        $line = $this->getFixtureOrder()->getLines()->first();
        $editUrl = $this->getEditUrl($line);

        $this->authenticateUser();
        $this->client->request(Request::METHOD_GET, $editUrl);
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.save'), [
            'order_line[label]' => $newLabel = 'Lorem ipsum',
        ]);

        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_edit', ['order' => $line->getOrder()->getId()])));
        $this->assertHasFlash('success', 'success.order_line.updated');

        $line = $this->getFixtureOrder()->getLines()->first();
        $this->assertEquals($newLabel, $line->getLabel());
    }

    public function testCreate()
    {
        $this->authenticateUser();
        $order = $this->getFixtureOrder();
        $lineCount = $order->getLines()->count();
        foreach ([OrderLine::TYPE_SIMPLE, OrderLine::TYPE_ALLOWANCE, OrderLine::TYPE_ACTIVITY_SUBSCRIPTION] as $type) {
            $this->client->request(Request::METHOD_GET, $this->getCreateUrl($order, $type));
            $this->assertResponseIsSuccessful();
            $this->client->followRedirects(false);
            $fieldValues = ['order_line[label]' => 'Test'];
            if (OrderLine::TYPE_ALLOWANCE === $type) {
                $fieldValues['order_line[allowancePercentage]'] = 5;
            } elseif (OrderLine::TYPE_ACTIVITY_SUBSCRIPTION === $type) {
                $fieldValues = [];
            }
            $this->client->submitForm($this->trans('_meta.word.save'), $fieldValues);
            ++$lineCount;
            $this->assertEquals($lineCount, $this->getFixtureOrder()->getLines()->count());
            $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_edit', ['order' => $order->getId()])));
            $this->assertHasFlash('success', 'success.order_line.created');
        }
    }

    public function testDelete()
    {
        $this->authenticateUser();
        $order = $this->getFixtureOrder();
        $line = $order->getLines()->first();
        $count = $order->getLines()->count();
        $this->client->request(Request::METHOD_GET, $this->getDeleteUrl($line));
        $this->assertResponseIsSuccessful();

        $this->client->followRedirects(false);
        $this->client->submitForm($this->trans('_meta.word.delete'), []);

        $this->assertEquals($count - 1, $this->getFixtureOrder()->getLines()->count());
        $this->assertTrue($this->client->getResponse()->isRedirect($this->getUrl('admin_order_edit', ['order' => $order->getId()])));
        $this->assertHasFlash('success', 'success.order_line.deleted');
    }

    protected function getFixtureOrder(?int $id = null): Order
    {
        return $this->getDoctrine()->getRepository(Order::class)->findOneBy($id ? ['id' => $id] : []);
    }

    protected function getEditUrl(OrderLine $line): string
    {
        return $this->getUrl('admin_order_line_edit', ['order' => $line->getOrder()->getId(), 'line' => $line->getId()]);
    }

    protected function getCreateUrl(Order $order, string $type): string
    {
        return $this->getUrl('admin_order_line_create', ['order' => $order->getId(), 'type' => $type]);
    }

    protected function getDeleteUrl(OrderLine $line): string
    {
        return $this->getUrl('admin_order_line_delete', ['order' => $line->getOrder()->getId(), 'line' => $line->getId()]);
    }
}
