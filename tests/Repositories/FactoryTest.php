<?php

namespace Weblab\MoneyBird\Repositories;

use Weblab\MoneyBird\Exceptions\NonExistingRepositoryException;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class FactoryTest
 * @author Weblab.nl - Eelco Verbeek
 */
class FactoryTest extends TestCase {

    /** @test */
    public function get_repository() {
        // Make instance of Factory
        $factory = new Factory($this->getMoneyBirdMock());

        // Assert if get return the correct class
        $this->assertInstanceOf(Invoices::class, $factory->get('invoices'));
    }

    /** @test */
    public function non_existing_repository() {
        // Expect an Exception to be thrown
        $this->expectException(NonExistingRepositoryException::class);

        // Make instance of Factory
        $factory = new Factory($this->getMoneyBirdMock());

        // Run code
        $factory->get('games');
    }

}
