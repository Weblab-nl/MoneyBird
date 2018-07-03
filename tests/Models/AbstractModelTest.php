<?php

namespace Weblab\MoneyBird\Models;
use Weblab\CURL\Result;
use Weblab\MoneyBird\Exceptions\EntityCreationException;
use Weblab\MoneyBird\Exceptions\EntityDeleteException;
use Weblab\MoneyBird\Exceptions\EntityUpdateException;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class AbstractModelTest
 * @author Weblab.nl - Eelco Verbeek
 */
class AbstractModelTest extends TestCase {

    /** @test */
    public function findSuccess() {
        // Get a mock for CURL\Result
        $moneyBirdResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Set assertions
        $moneyBirdResult->expects($this->once())->method('getStatus')->willReturn(200);
        $moneyBirdResult->expects($this->once())->method('getResult')->willReturn(new \stdClass());

        // Get a MoneyBird mock
        $moneyBird = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBird->expects($this->once())->method('get')->willReturn($moneyBirdResult);

        // Make instance of class that extend the AbstractModel
        $model = new FakeFind($moneyBird);

        // Run code
        $result = $model->find(1);

        // Assert if result is instance of FakeFind
        $this->assertInstanceOf(FakeFind::class, $result);
    }

    /** @test */
    public function findFail() {
        // Get a mock for CURL\Result
        $moneyBirdResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $moneyBirdResult->expects($this->once())->method('getStatus')->willReturn(404);

        // Get MoneyBird mock
        $moneyBird = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBird->expects($this->once())->method('get')->willReturn($moneyBirdResult);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBird);

        // Run code
        $result = $model->find(1);

        // Assert if the result was null
        $this->assertNull($result);
    }

    /** @test */
    public function saveNewEntitySuccess() {
        // Make a test entity
        $entity = (object) ['name' => 'test'];
        // Set saveType
        $saveType = 'post';
        // Set the expected endpoint url
        $expectedURL = '/endpoint';
        // Set CURL result status
        $resultStatus = 201;

        // Set CURL result
        $responseResult = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        // Get CURL\Result mock
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $responseMock->expects($this->once())->method('getStatus')->willReturn($resultStatus);
        $responseMock->expects($this->once())->method('getResult')->willReturn($responseResult);

        // Get MoneyBird mock
        $moneyBirdMock = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBirdMock->expects($this->once())->method($saveType)->with($expectedURL, json_encode(['entity' => $entity]), [], [])->willReturn($responseMock);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBirdMock, $entity);

        // Run code
        $result = $model->save();

        // Validate if result was true
        $this->assertTrue($result);
    }

    /** @test */
    public function saveUpdateEntitySuccess() {
        // Set test entity
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        // Set expected entity
        $expectedEntity = (object) [
            'name'  => 'not test anymore',
            'id'    => 666
        ];

        // Set the expected save method name
        $saveType = 'patch';
        // Set the expected endpoint url
        $expectedURL = '/endpoint/666';
        // Set the CURL\Result http status
        $resultStatus = 200;

        // Set the CURL\Result data
        $responseResult = (object) [
            'id'    => 666,
            'name'  => 'not test anymore'
        ];

        // Get a CURL\Result mock
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $responseMock->expects($this->once())->method('getStatus')->willReturn($resultStatus);
        $responseMock->expects($this->once())->method('getResult')->willReturn($responseResult);

        // Get MoneyBird mock
        $moneyBirdMock = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBirdMock->expects($this->once())->method($saveType)->with($expectedURL, json_encode(['entity' => $expectedEntity]), [], [])->willReturn($responseMock);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBirdMock, $entity, true);

        // Change the value of a property
        $model->name = 'not test anymore';

        // Run the code
        $result = $model->save();

        // Assert if result is true
        $this->assertTrue($result);
    }

    /** @test */
    public function saveCreateNewEntityFail() {
        // Get CURL\Result mock
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $responseMock->expects($this->once())->method('getStatus')->willReturn(400);

        // Set test entity
        $entity = (object) [
            'name'  => 'test'
        ];

        // Get MoneyBird mock
        $moneyBirdMock = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBirdMock->expects($this->once())->method('post')->with('/endpoint', json_encode(['entity' => $entity]), [], [])->willReturn($responseMock);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBirdMock, $entity);

        // Expect an Exception to be thrown
        $this->expectException(EntityCreationException::class);

        // Run code
        $model->save();
    }

    /** @test */
    public function saveUpdateEntityFail() {
        // Get CURL\Result mock
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $responseMock->expects($this->once())->method('getStatus')->willReturn(400);

        // Set test entity
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        // Set expected entity
        $expectedEntity = (object) [
            'name'  => 'not test anymore',
            'id'    => 666
        ];

        // Get MoneyBird mock
        $moneyBirdMock = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBirdMock->expects($this->once())->method('patch')->with('/endpoint/666', json_encode(['entity' => $expectedEntity]), [], [])->willReturn($responseMock);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBirdMock, $entity, true);

        // Expect an Exception to be thrown
        $this->expectException(EntityUpdateException::class);

        // Change property
        $model->name = 'not test anymore';

        // Run code
        $model->save(true);
    }

    /** @test */
    public function deleteSuccess() {
        // Set test entity
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        // Get CURL\Result mock
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $responseMock->expects($this->once())->method('getStatus')->willReturn(204);

        // Get MoneyBird mock
        $moneyBirdMock = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBirdMock->expects($this->once())->method('delete')->with('/endpoint/666', [], [], [])->willReturn($responseMock);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBirdMock, $entity);

        // Run code
        $result = $model->delete();

        // Assert if result is true
        $this->assertTrue($result);
    }

    /** @test */
    public function deleteFailure() {
        // Set test entity
        $entity = (object) [
            'id'    => 666,
            'name'  => 'test'
        ];

        // Get CURL\Result mock
        $responseMock = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        // Set assertions
        $responseMock->expects($this->once())->method('getStatus')->willReturn(404);

        // Get MoneyBird mock
        $moneyBirdMock = $this->getMoneyBirdMock();
        // Set assertions
        $moneyBirdMock->expects($this->once())->method('delete')->with('/endpoint/666', [], [], [])->willReturn($responseMock);

        // Make instance of class that extends AbstractModel
        $model = new FakeFind($moneyBirdMock, $entity);

        // Expect an Exceptions to be thrown
        $this->expectException(EntityDeleteException::class);

        // Run code
        $model->delete();
    }

}

class FakeFind extends AbstractModel {

    const ENTITY = 'entity';
    const ENDPOINT = 'endpoint';

}
