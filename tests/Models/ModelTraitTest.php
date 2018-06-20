<?php

namespace Weblab\MoneyBird\Models;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class ModelTrait
 * @author Weblab.nl - Eelco Verbeek
 */
class ModelTraitTest extends TestCase {

    /** @test */
    public function constructFromAPI() {
        // Set test entity
        $entity = (object) [
            'id'    => 1,
            'name'  => 'test'
        ];

        // Get mock of ModelTrait
        $model = $this->getMockBuilder(ModelTrait::class)
            ->setConstructorArgs([$entity, true])
            ->getMockForTrait();

        // Assert if constructed correctly
        $this->assertEquals($entity->id, $model->id);
        $this->assertEquals($entity->name, $model->name);
    }

    /** @test */
    public function constructWithMutableRestrictions() {
        // Set test entity
        $entity = (object) [
            'id'    => 1,
            'name'  => 'test'
        ];

        // Get mock of ModelTrait
        $model = $this->getMockBuilder(ModelTrait::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMutable'])
            ->getMockForTrait();

        // Set assertions
        $model->expects($this->exactly(2))->method('isMutable')->willReturnOnConsecutiveCalls([true, false]);

        // Run constructor of ModelTrait again after assertions were added the the mock
        $reflectedClass = new \ReflectionClass($model);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($model, clone $entity);

        // Assert if constructed correctly
        $this->assertEquals($entity->id, $model->id);
        $this->assertNotEquals($entity->name, $model->name);
    }

}
