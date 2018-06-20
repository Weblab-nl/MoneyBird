<?php

namespace Weblab\MoneyBird;

use Weblab\MoneyBird\Repositories\Factory;
use Weblab\MoneyBird\Repositories\Invoices;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class MoneyBird2Test
 * @author Weblab.nl - Eelco Verbeek
 */
class MoneyBirdTest extends TestCase {

    /** @test */
    public function access_repository() {
        // Create a MoneyBird instance
        $moneyBird = new MoneyBird('126abf', 'this is very secret', 126);

        // Get a RepositoryFactory mock
        $factory = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Get a InvoicesRepository mock
        $repository = $this->getMockBuilder(Invoices::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Setup assertions
        $factory->expects($this->once())->method('get')->with('invoices')->willReturn($repository);

        // Inject the factory into the MoneyBird class
        $moneyBird->setRepositoryFactory($factory);

        // Assert if the factory returns the repository
        $this->assertEquals($repository, $moneyBird->invoices);
    }

}
