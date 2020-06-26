<?php

namespace Weblab\MoneyBird\Models;

use Weblab\MoneyBird\MoneyBird;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class InvoiceTest
 * @author Weblab.nl - Eelco Verbeek
 */
class InvoiceTest extends TestCase {

    /** @test */
    public function addPayment() {
        $expectedPath   = 'sales_invoices/6/payments';
        $expectedParam  = json_encode(['payment' => ['price' => 30.25]]);

        $payment = $this->getMockBuilder(Payment::class)->setConstructorArgs([(object) ['price' => 30.25]])->getMock();
        $payment->expects($this->once())->method('toJSON')->with()->willReturn($expectedParam);


        $api = $this->getMockBuilder(MoneyBird::class)->disableOriginalConstructor()->getMock();
        $api->expects($this->once())->method('post')->with($expectedPath, $expectedParam)->willReturn(true);

        $invoice = new Invoice($api, (object) ['id' => 6, 'details' => []], true);

        $invoice->addPayment($payment);
    }

    /** @test */
    public function send() {
        $expectedPath   = 'sales_invoices/6/send_invoice';
        $expectedParam  = json_encode(['delivery_method' => 'Manual']);

        $sendSettings = $this->getMockBuilder(SendSettings::class)->setConstructorArgs([(object) ['delivery_method' => 'Manual']])->getMock();
        $sendSettings->expects($this->once())->method('toJSON')->with()->willReturn($expectedParam);


        $api = $this->getMockBuilder(MoneyBird::class)->disableOriginalConstructor()->getMock();
        $api->expects($this->once())->method('patch')->with($expectedPath, $expectedParam)->willReturn(true);

        $invoice = new Invoice($api, (object) ['id' => 6, 'details' => []], true);

        $invoice->send($sendSettings);
    }

    /** @test */
    public function delete_payments() {
        $expectedGetPath = 'sales_invoices/66/payments';
        $expectedDeletePath = 'sales_invoices/66/payments/666';

        $payment = new \stdClass();
        $payment->id = 666;

        $payments = [$payment];

        $getResult = $this->getMockBuilder(\Weblab\CURL\Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getResult'])
            ->getMock();

        $getResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);

        $getResult
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($payments);

        $deleteResult = $this->getMockBuilder(\Weblab\CURL\Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus'])
            ->getMock();

        $deleteResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(204);

        $api = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();

        $api
            ->expects($this->once())
            ->method('get')
            ->with($expectedGetPath)
            ->willReturn($getResult);

        $api
            ->expects($this->once())
            ->method('delete')
            ->with($expectedDeletePath)
            ->willReturn($deleteResult);

        $api->payments = new \Weblab\MoneyBird\Repositories\Payments($api);

        $invoice = new Invoice($api, (object) ['id' => 66, 'details' => []], true);
        $invoice->deletePayments();
    }

}
