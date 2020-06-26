<?php

namespace Weblab\MoneyBird\Repositories;

use Weblab\CURL\Result;
use Weblab\MoneyBird\Models\Contact;
use Weblab\MoneyBird\MoneyBird;
use Weblab\MoneyBird\Tests\TestCase;

/**
 * Class ContactsTest
 * @author Weblab.nl - Eelco Verbeek
 */
class ContactsTest extends TestCase {

    /** @test */
    public function searchContactSuccess() {
        $apiResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getResult'])
            ->getMock();

        $apiResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);

        $apiResult
            ->expects($this->once())
            ->method('getResult')
            ->willReturn([(object) [
                'id'    => 6,
                'name'  => 'weblab'
            ]]);

        $api = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $api
            ->expects($this->once())
            ->method('get')
            ->with('/' . Contact::ENDPOINT, ['query' => 'searchstring'])
            ->willReturn($apiResult);

        $contacts = new Contacts($api);
        $result = $contacts->search('searchstring');

        $this->assertInternalType('array', $result);

        foreach($result as $contact) {
            $this->assertEquals(6, $contact->id);
            $this->assertEquals('weblab', $contact->name);
        }
    }

    /** @test */
    public function searchContactFailure() {
        $apiResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus'])
            ->getMock();

        $apiResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(504);


        $api = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $api
            ->expects($this->once())
            ->method('get')
            ->with('/' . Contact::ENDPOINT, ['query' => 'searchstring'])
            ->willReturn($apiResult);

        $contacts = new Contacts($api);
        $result = $contacts->search('searchstring');

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function searchContactNoResults() {
        $apiResult = $this->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->setMethods(['getStatus', 'getResult'])
            ->getMock();

        $apiResult
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(200);

        $apiResult
            ->expects($this->once())
            ->method('getResult')
            ->willReturn([]);

        $api = $this->getMockBuilder(MoneyBird::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $api
            ->expects($this->once())
            ->method('get')
            ->with('/' . Contact::ENDPOINT, ['query' => 'searchstring'])
            ->willReturn($apiResult);

        $contacts = new Contacts($api);
        $result = $contacts->search('searchstring');

        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

}
