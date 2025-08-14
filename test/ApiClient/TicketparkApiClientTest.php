<?php

namespace Ticketpark\ApiClient\Test;

use PHPUnit\Framework\TestCase;
use Prophecy\Prophet;
use Ticketpark\ApiClient\Exception\TokenGenerationException;
use Ticketpark\ApiClient\Exception\UnexpectedResponseException;
use Ticketpark\ApiClient\Http\ClientInterface;
use Ticketpark\ApiClient\Http\Response;
use Ticketpark\ApiClient\TicketparkApiClient;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClientTest extends TestCase
{
    private TicketparkApiClient $apiClient;
    private Prophet $prophet;

    public function setUp(): void
    {
        $this->apiClient = new TicketparkApiClient('apiKey', 'apiSecret');
        $this->prophet = new Prophet();
    }

    protected function tearDown(): void
    {
        $this->addToAssertionCount(count($this->prophet->getProphecies()));
    }

    public function testSetAccessToken()
    {
        $expiration = new \DateTime('2035-01-01 12:00:00');
        $this->apiClient->setAccessToken('some-token', $expiration);
        
        $this->assertInstanceOf(AccessToken::class, $this->apiClient->getAccessToken());
        $this->assertEquals('some-token', $this->apiClient->getAccessToken()->getToken());
        $this->assertEquals($expiration, $this->apiClient->getAccessToken()->getExpiration());
    }

    public function testSetRefreshToken()
    {
        $expiration = new \DateTime('2035-01-01 12:00:00');
        $this->apiClient->setRefreshToken('some-token', $expiration);

        $this->assertInstanceOf(RefreshToken::class, $this->apiClient->getRefreshToken());
        $this->assertEquals('some-token', $this->apiClient->getRefreshToken()->getToken());
        $this->assertEquals($expiration, $this->apiClient->getRefreshToken()->getExpiration());
    }

    public function testGenerateTokensWithoutDataThrowsException()
    {
        $this->expectException(TokenGenerationException::class);

        $this->apiClient->generateTokens();
    }

    public function testHead()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->head(
            'https://api.ticketpark.ch/path?foo=bar',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer some-token'
            ]
        )
        ->willReturn(new Response(200, '', []))
        ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setAccessToken('some-token');
        $this->apiClient->head('/path', ['foo' => 'bar']);
    }

    public function testGet()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->get(
            'https://api.ticketpark.ch/path?foo=bar',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer some-token'
            ]
        )
            ->willReturn(new Response(200, '', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setAccessToken('some-token');
        $this->apiClient->get('/path', ['foo' => 'bar']);
    }

    public function testPost()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->post(
            'https://api.ticketpark.ch/path',
            '{"foo":"bar"}',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer some-token'
            ]
        )
            ->willReturn(new Response(204, '', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setAccessToken('some-token');
        $this->apiClient->post('/path', ['foo' => 'bar']);
    }

    public function testPatch()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->patch(
            'https://api.ticketpark.ch/path',
            '{"foo":"bar"}',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer some-token'
            ]
        )
            ->willReturn(new Response(204, '', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setAccessToken('some-token');
        $this->apiClient->patch('/path', ['foo' => 'bar']);
    }

    public function testDelete()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->delete(
            'https://api.ticketpark.ch/path',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer some-token'
            ]
        )
            ->willReturn(new Response(204, '', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setAccessToken('some-token');
        $this->apiClient->delete('/path');
    }

    public function testGenerateTokensWithUsername()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->postForm(
            'https://api.ticketpark.ch/oauth/v2/token',
            [
                'username' => 'username',
                'password' => 'secret',
                'grant_type' => 'password'
            ],
            [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('apiKey:apiSecret')
            ]
        )
            ->willReturn(new Response(204, '{"access_token": "some-token", "refresh_token": "some-other-token", "expires_in": 600}', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setUserCredentials('username', 'secret');
        $this->apiClient->generateTokens();
    }

    public function testGenerateTokensWithRefreshToken()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->postForm(
            'https://api.ticketpark.ch/oauth/v2/token',
            [
                'refresh_token' => 'some-refresh-token',
                'grant_type' => 'refresh_token'
            ],
            [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('apiKey:apiSecret')
            ]
        )
            ->willReturn(new Response(204, '{"access_token": "some-token", "refresh_token": "some-other-token", "expires_in": 600}', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setRefreshToken('some-refresh-token');
        $this->apiClient->generateTokens();
    }

    public function testGenerateTokensThrowsExceptionOnUnexpectedResponse()
    {
        $this->expectException(UnexpectedResponseException::class);

        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->postForm(
            'https://api.ticketpark.ch/oauth/v2/token',
            [
                'username' => 'username',
                'password' => 'secret',
                'grant_type' => 'password'
            ],
            [
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'Accept'        => 'application/json',
                'Authorization' => 'Basic ' . base64_encode('apiKey:apiSecret')
            ]
        )
            ->willReturn(new Response(204, '{}', []))
            ->shouldBeCalledOnce();

        $this->apiClient->setClient($httpClient->reveal());
        $this->apiClient->setUserCredentials('username', 'secret');
        $this->apiClient->generateTokens();
    }

    public function testGetWithOverwrittenBaseUrl()
    {
        $httpClient = $this->prophet->prophesize(ClientInterface::class);
        $httpClient->get(
            'https://overwritten.ticketpark.ch/path?foo=bar',
            [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Authorization' => 'Bearer some-token'
            ]
        )
            ->willReturn(new Response(200, '', []))
            ->shouldBeCalledOnce();

        $apiClient = new TicketparkApiClient(
            'apiKey',
            'apiSecret',
            'https://overwritten.ticketpark.ch'
        );

        $apiClient->setClient($httpClient->reveal());
        $apiClient->setAccessToken('some-token');
        $apiClient->get('/path', ['foo' => 'bar']);
    }
}
