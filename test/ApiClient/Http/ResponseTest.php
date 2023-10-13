<?php

namespace Ticketpark\ApiClient\Test\Http;

use PHPUnit\Framework\TestCase;
use Ticketpark\ApiClient\Http\Response;

class ResponseTest extends TestCase
{
    public function testItReturnsStatusCode()
    {
        $response = new Response(
            200,
            '',
            []
        );

        $this->assertSame(200, $response->getStatusCode());
    }

    public function testItReturnsContentAsArray()
    {
        $response = new Response(
            200,
            '{"foo": "bar"}',
            []
        );

        $this->assertSame(['foo' => 'bar'], $response->getContent());
    }

    public function testItReturnsTrueOnSuccessfulStatusCode()
    {
        $statusCode = 200;
        while ($statusCode <= 204) {

            $response = new Response(
                $statusCode,
                '',
                []
            );

            $this->assertTrue($response->isSuccessful());
            $statusCode++;
        }
    }

    public function testItReturnsFalseOnUnsuccessfulStatusCode()
    {
        $statusCode = 100;
        while ($statusCode <= 999) {
            if ($statusCode < 200 || $statusCode > 204) {
                $response = new Response(
                    $statusCode,
                    '',
                    []
                );
                $this->assertFalse($response->isSuccessful());
            }
            $statusCode++;
        }
    }

    public function testGettingGeneratedPid()
    {
        $response = new Response(
            204,
            '',
            [
                'Some-Header' => ['something'],
                'Location' => ['https://api.ticketpark.ch/some-entity/some-uuid']
            ]
        );

        $this->assertSame('some-uuid', $response->getGeneratedPid());
    }

    public function testGettingGeneratedPidReturnsNullIfInexistent()
    {
        $response = new Response(
            204,
            '',
            [
                'Some-Header' => ['something']
            ]
        );

        $this->assertNull($response->getGeneratedPid());
    }

    public function testGettingGeneratedPidReturnsNullIfListLinkAvailable()
    {
        $response = new Response(
            204,
            '',
            [
                'Some-Header' => ['something'],
                'Location' => ['https://api.ticketpark.ch/some-entity/filters[batchId]=some-uuid&orderBy[batchOrder]=asc']
            ]
        );

        $this->assertNull($response->getGeneratedPid());
    }

    public function testGettingGeneratedListLink()
    {
        $response = new Response(
            204,
            '',
            [
                'Some-Header' => ['something'],
                'Location' => ['https://api.ticketpark.ch/some-entity/filters[batchId]=some-uuid&orderBy[batchOrder]=asc']
            ]
        );

        $this->assertSame('/some-entity/filters[batchId]=some-uuid&orderBy[batchOrder]=asc', $response->getGeneratedListLink());
    }

    public function testGettingGeneratedListLinkReturnsNullIfInexistent()
    {
        $response = new Response(
            204,
            '',
            [
                'Some-Header' => ['something']
            ]
        );

        $this->assertNull($response->getGeneratedListLink());
    }

    public function testGettingGeneratedListLinkReturnsNullIfPidAvailable()
    {
        $response = new Response(
            204,
            '',
            [
                'Some-Header' => ['something'],
                'Location' => ['https://api.ticketpark.ch/some-entity/some-uuid']
            ]
        );

        $this->assertNull($response->getGeneratedListLink());
    }
}