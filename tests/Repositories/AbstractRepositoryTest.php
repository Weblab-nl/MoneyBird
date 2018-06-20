<?php

namespace Weblab\MoneyBird\Test\Repositories;

use Weblab\MoneyBird\Models\AbstractModel;
use Weblab\MoneyBird\Repositories\AbstractRepository;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class AbstractRepositoryTest
 * @author Weblab.nl - Eelco Verbeek
 */
class AbstractRepositoryTest extends TestCase {

    /** @test */
    public function create_new() {
        // Get MoneyBird mock
        $moneyBird = $this->getMoneyBirdMock();

        // Make instance of class that extends AbstractRepository
        $repository = new FakeRepository($moneyBird);

        // Set test entity
        $data = [
            'contact_id'    => 1
        ];

        // Run code
        $result = $repository->create($data);

        // Assert if result instance of FakeEntity and if it was constructed correctly correctly
        $this->assertInstanceOf(FakeEntity::class, $result);
        $this->assertEquals($data['contact_id'], $result->contact_id);
    }
    
    /** @test */
    public function find_entity() {
        // Get MoneyBird mock
        $moneyBird = $this->getMoneyBirdMock();

        // Set the id that is searched for
        $id = 6;

        // Make instance of class that extends AbstractRepository
        $repository = new FakeRepository($moneyBird);

        // Run code
        $result = $repository->find($id);

        // Assert if result instance of FakeEntity and if it was constructed correctly correctly
        $this->assertInstanceOf(FakeEntity::class, $result);
        $this->assertEquals($id, $result->id);
    }

    /** @test */
    public function entity_not_found() {
        // Get MoneyBird mock
        $moneyBird = $this->getMoneyBirdMock();

        // Set the id that is searched for
        $id = 6;

        // Make instance of class that extends AbstractRepository
        $repository = new FakeRepositoryEntityNotFound($moneyBird);

        // Run code
        $result = $repository->find($id);

        // Assert if the result equals null
        $this->assertEquals(null, $result);
    }

}

class FakeRepository extends AbstractRepository {

    protected function getModelClass() {
        return FakeEntity::class;
    }

}

class FakeEntity extends AbstractModel {

    public $id;

    public function find($id) {
        $this->id = $id;
        return true;
    }

}

class FakeRepositoryEntityNotFound extends AbstractRepository {

    protected function getModelClass() {
        return FakeEnityNotFound::class;
    }

}

class FakeEnityNotFound extends AbstractModel {

    public function find($id) {
        return false;
    }

}
