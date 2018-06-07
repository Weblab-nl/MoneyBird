<?php

namespace Weblab\MoneyBird;

use Prophecy\Argument;
use Weblab\MoneyBird\Exceptions\TooManyRequestsException;
use Weblab\CURL\Result;
use Weblab\MoneyBird\Tests\TestCase;
use Weblab\RESTClient\Adapters\AdapterInterface;

/**
 * Class MoneyBirdTest
 * @author Weblab.nl - Eelco Verbeek
 */
class MoneyBirdTest extends TestCase {

    /**
     * @runInSeparateProcess
     */
    public function testTooManyRequestsException() {
        // make the blueprint of a result and set what it will return
        $result = $this->prophesize(Result::class);
        $result->getStatus()
            ->shouldBeCalled()
            ->willReturn(429);

        // make the blueprint of the adapter and set what it will return
        $adapter = $this->prophesize(AdapterInterface::class);
        $adapter->doRequest('get', Argument::containingString('/users/1'), Argument::cetera())
            ->shouldBeCalled()
            ->willReturn($result);

        // add the config alias to the mock-class
        $config = \Mockery::mock('alias:' . '\Config');
        $config
            ->shouldReceive('get')
            ->andReturns('config');

        // initiate a new Moneybird object and set its adapter
        $moneyBird = new MoneyBird('1a2b3c', 'this is a very secret secret',123456);
        $moneyBird->setAdapter($adapter->reveal());

        // set the expectation of an exception and test it
        $this->expectException(TooManyRequestsException::class);
        $moneyBird->get('/users/1');
    }

}
