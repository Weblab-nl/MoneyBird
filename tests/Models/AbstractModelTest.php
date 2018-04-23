<?php

namespace Weblab\MoneyBird\Models;

use Weblab\MoneyBird\Exceptions\EntityCreationException;
use Weblab\MoneyBird\Exceptions\EntityDeleteException;
use Weblab\MoneyBird\Exceptions\EntityUpdateException;
use Weblab\MoneyBird\MoneyBird;
use Weblab\MoneyBird\Tests\TestCase;
use Weblab\CURL\Result;

/**
 * Class AbstractModelTest
 * @author Weblab.nl - Eelco Verbeek
 */
class AbstractModelTest extends TestCase {

    public function testConstuctFromAPI() {
        $entity = (object) [
            'id'    => 1,
            'name'  => 'test'
        ];

        $model = $this->getMockBuilder(AbstractModel::class)
            ->setConstructorArgs([clone $entity, true])
            ->getMockForAbstractClass();

        $model->name = 'not test';

        $this->assertAttributeEquals($entity, 'old', $model);
        $this->assertAttributeNotEquals($entity, 'entity', $model);
    }

    public function testConstructWithMutableRestrictions() {
        $entity = (object) [
            'id'    => 1,
            'name'  => 'test'
        ];

        $model = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['isMutable'])
            ->getMockForAbstractClass();

        $model
            ->expects($this->exactly(2))
            ->method('isMutable')
            ->willReturnOnConsecutiveCalls([true, false]);

        $reflectedClass = new \ReflectionClass(AbstractModel::class);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($model, clone $entity);

        unset($entity->name);

        $this->assertAttributeEquals($entity, 'entity', $model);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFindSuccess() {
        $moneyBirdResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);
        $moneyBirdResult
            ->expects($this->once())
            ->method('getResult')
            ->willReturn(new \stdClass());


        $moneyBird = \Mockery::mock('alias:' . '\Weblab\MoneyBird\MoneyBird');
        $moneyBird
            ->shouldReceive('getInstance')
            ->andReturns($moneyBird);
        $moneyBird
            ->shouldReceive('get')
            ->andReturns($moneyBirdResult);


        $result = FakeFind::find(1);
        $this->assertInstanceOf(FakeFind::class, $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testFindFail() {
        $moneyBirdResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(404);


        $moneyBird = \Mockery::mock('alias:' . '\Weblab\MoneyBird\MoneyBird');
        $moneyBird
            ->shouldReceive('getInstance')
            ->andReturns($moneyBird);
        $moneyBird
            ->shouldReceive('get')
            ->andReturns($moneyBirdResult);


        $result = FakeFind::find(1);
        $this->assertNull($result);
    }

    public function testSaveNewEntitySuccess() {
        $entity = (object) ['name' => 'test'];
        $saveType = 'post';
        $expectedURL = '/endpoint.json';
        $resultStatus = 201;

        $responseResult = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn($resultStatus);
        $responseMock
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($responseResult);


        $moneyBirdMock = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdMock
            ->expects($this->once())
            ->method($saveType)
            ->with($expectedURL, json_encode(['entity' => $entity]), [], [])
            ->willReturn($responseMock);


        $model = $this->getMockBuilder(FakeFind::class)
            ->setConstructorArgs([$entity])
            ->setMethods(['connection'])
            ->getMockForAbstractClass();
        $model
            ->expects($this->once())
            ->method('connection')
            ->willReturn($moneyBirdMock);

        $result = $model->save();

        $this->assertTrue($result);
    }

    public function testSaveUpdateEntitySuccess() {
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        $expectedEntity = (object) [
            'name'  => 'not test anymore',
            'id'    => 666
        ];

        $saveType = 'patch';
        $expectedURL = '/endpoint/666.json';
        $resultStatus = 200;

        $responseResult = (object) [
            'id'    => 666,
            'name'  => 'not test anymore'
        ];

        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn($resultStatus);
        $responseMock
            ->expects($this->once())
            ->method('getResult')
            ->willReturn($responseResult);


        $moneyBirdMock = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdMock
            ->expects($this->once())
            ->method($saveType)
            ->with($expectedURL, json_encode(['entity' => $expectedEntity]), [], [])
            ->willReturn($responseMock);


        $model = $this->getMockBuilder(FakeFind::class)
            ->setConstructorArgs([$entity, true])
            ->setMethods(['connection'])
            ->getMockForAbstractClass();
        $model
            ->expects($this->once())
            ->method('connection')
            ->willReturn($moneyBirdMock);

        $model->name = 'not test anymore';

        $result = $model->save();

        $this->assertTrue($result);
    }

    public function testSaveCreateNewEntityFail() {
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(400);


        $entity = (object) [
            'name'  => 'test'
        ];

        $moneyBirdMock = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdMock
            ->expects($this->once())
            ->method('post')
            ->with('/endpoint.json', json_encode(['entity' => $entity]), [], [])
            ->willReturn($responseMock);


        $model = $this->getMockBuilder(FakeFind::class)
            ->setConstructorArgs([$entity])
            ->setMethods(['connection'])
            ->getMockForAbstractClass();
        $model
            ->expects($this->once())
            ->method('connection')
            ->willReturn($moneyBirdMock);

        $this->expectException(EntityCreationException::class);

        $model->save();
    }

    public function testSaveUpdateEntityFail() {
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(400);


        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        $expectedEntity = (object) [
            'name'  => 'not test anymore',
            'id'    => 666
        ];

        $moneyBirdMock = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdMock
            ->expects($this->once())
            ->method('patch')
            ->with('/endpoint/666.json', json_encode(['entity' => $expectedEntity]), [], [])
            ->willReturn($responseMock);


        $model = $this->getMockBuilder(FakeFind::class)
            ->setConstructorArgs([$entity, true])
            ->setMethods(['connection'])
            ->getMockForAbstractClass();
        $model
            ->expects($this->once())
            ->method('connection')
            ->willReturn($moneyBirdMock);

        $this->expectException(EntityUpdateException::class);

        $model->name = 'not test anymore';

        $model->save(true);
    }

    public function testDeleteSuccess() {
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(204);


        $moneyBirdMock = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdMock
            ->expects($this->once())
            ->method('delete')
            ->with('/endpoint/666.json', [], [], [])
            ->willReturn($responseMock);


        $model = $this->getMockBuilder(FakeFind::class)
            ->setConstructorArgs([$entity])
            ->setMethods(['connection'])
            ->getMockForAbstractClass();
        $model
            ->expects($this->once())
            ->method('connection')
            ->willReturn($moneyBirdMock);

        $result = $model->delete();

        $this->assertTrue($result);
    }

    public function testDeleteFailure() {
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $responseMock
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(404);


        $moneyBirdMock = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->getMock();
        $moneyBirdMock
            ->expects($this->once())
            ->method('delete')
            ->with('/endpoint/666.json', [], [], [])
            ->willReturn($responseMock);


        $model = $this->getMockBuilder(FakeFind::class)
            ->setConstructorArgs([$entity])
            ->setMethods(['connection'])
            ->getMockForAbstractClass();
        $model
            ->expects($this->once())
            ->method('connection')
            ->willReturn($moneyBirdMock);

        $this->expectException(EntityDeleteException::class);
        $model->delete();
    }



}

class FakeFind extends AbstractModel {

    const ENTITY = 'entity';
    const ENDPOINT = 'endpoint';

}
