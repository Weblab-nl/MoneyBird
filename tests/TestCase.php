<?php

namespace Weblab\MoneyBird\Tests;

use Weblab\MoneyBird\MoneyBird;

/**
 * Class TestCase
 * @author Weblab.nl - Eelco Verbeek
 */
class TestCase extends \PHPUnit\Framework\TestCase {

    public function getMoneyBirdMock() {
        return $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

}
