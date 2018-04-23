<?php

namespace Weblab\MoneyBird\Models;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class AbstractInvoice
 * @author Weblab.nl - Eelco Verbeek
 */
class AbstractInvoiceTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testToArray() {
        // set the product entity
        $productEntity = [
            'id'    => 333,
            'name'  => 'product'
        ];

        // get the mock product
        $product = $this->getMockBuilder(Product::class)
            ->disableOriginalConstructor()
            ->getMock();

        // set the expected results from the object
        $product
            ->expects($this->once())
            ->method('toArray')
            ->willReturn($productEntity);

        // set an entity
        $entity = [
            'id'    => 666,
            'name'  => 'test'
        ];

        // set the invoice mock object
        $invoice = $this->getMockBuilder(AbstractInvoice::class)
            ->setConstructorArgs([(object) $entity])
            ->getMockForAbstractClass();

        // add a mock product tot the invoice
        $invoice->addProduct($product);

        // cast the invoice to an array
        $result = $invoice->toArray();

        // set the Expected results and add the productEntity to it
        $expectedResult = $entity;
        $expectedResult['details_attributes'] = [$productEntity];

        // assert that the expectedResults and result are equal
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFillFromAPI() {
        // set the product and entity array/object
        $product = [
            'id' => 333,
            'name' => 'product'
        ];

        $entity = (object) [
            'id' => 666,
            'name' => 'test',
            'details' => [
                $product
            ]
        ];

        // overwrite the product array and set it as Product mock
        $product = \Mockery::mock('overload:' . Product::class);


        $invoice = $this->getMockBuilder(AbstractInvoice::class)
            ->setConstructorArgs([clone $entity, true])
            ->getMockForAbstractClass();

        // set the expected Product and unset its details
        $expectedProduct = new Product($product);
        unset($entity->details);
        $expectedEntity = $entity;

        // assert that the expected entity and product are correct
        $this->assertAttributeEquals([$expectedProduct], 'products', $invoice);
        $this->assertAttributeEquals($expectedEntity, 'entity', $invoice);
    }
}
