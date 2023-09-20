<?php

namespace Ticketpark\ApiClient\Test;

use Buzz\Browser;
use Buzz\Message\Response;
use PHPUnit\Framework\TestCase;
use Ticketpark\ApiClient\Exception\TokenGenerationException;
use Ticketpark\ApiClient\TicketparkApiClient;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClientTest extends TestCase
{
    protected $apiClient;

    public function setUp(): void
    {
        $this->apiClient = new TicketparkApiClient('apiKey', 'apiSecret');
    }

    public function testDefaultBrowser()
    {
        $this->assertInstanceOf(Browser::class, $this->apiClient->getBrowser());
    }

    public function testSetAccessToken()
    {
        $this->apiClient->setAccessToken('foo');
        $this->assertInstanceOf(AccessToken::class, $this->apiClient->getAccessToken());
        $this->assertEquals('foo', $this->apiClient->getAccessToken()->getToken());
    }

    public function testSetAccessTokenInstance()
    {
        $accessToken = new AccessToken('bar');
        $this->apiClient->setAccessTokenInstance($accessToken);

        $this->assertInstanceOf(AccessToken::class, $this->apiClient->getAccessToken());
        $this->assertEquals('bar', $this->apiClient->getAccessToken()->getToken());
    }

    public function testSetRefreshToken()
    {
        $this->apiClient->setRefreshToken('foo');
        $this->assertInstanceOf(RefreshToken::class, $this->apiClient->getRefreshToken());
        $this->assertEquals('foo', $this->apiClient->getRefreshToken()->getToken());
    }

    public function testSetRefreshTokenInstance()
    {
        $refreshToken = new RefreshToken('bar');
        $this->apiClient->setRefreshTokenInstance($refreshToken);

        $this->assertInstanceOf(RefreshToken::class, $this->apiClient->getRefreshToken());
        $this->assertEquals('bar', $this->apiClient->getRefreshToken()->getToken());
    }

    public function testGenerateTokensWithoutData()
    {
        $this->expectException(TokenGenerationException::class);

        $this->apiClient->generateTokens();
    }

    public function testGenerateTokensWithUserCredentials()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'post' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo([
                        'username' => 'username',
                        'password' => 'password',
                        'grant_type' => 'password'
                    ]),
                ],
                'response' => [
                    'status' => 200,
                    'content' => [
                        'access_token' => 'accessToken',
                        'refresh_token' => 'refreshToken',
                        'expires_in' => 60
                    ]
                ]
            ]
        ]));

        $this->apiClient->setUserCredentials('username', 'password');
        $this->apiClient->generateTokens();

        $this->assertEquals('accessToken', $this->apiClient->getAccessToken()->getToken());
        $this->assertEquals('refreshToken', $this->apiClient->getRefreshToken()->getToken());
    }

    public function testGenerateTokensWithUserCredentialsFails()
    {
        $this->expectException(TokenGenerationException::class);

        $this->apiClient->setBrowser($this->getBrowserMock([
            'post' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo([
                        'username' => 'username',
                        'password' => 'password',
                        'grant_type' => 'password'
                    ]),
                ],
                'response' => [
                    'status' => 400,
                    'content' => ''
                ]
            ]
        ]));

        $this->apiClient->setUserCredentials('username', 'password');
        $this->apiClient->generateTokens();
    }

    public function testGenerateTokensWithRefreshToken()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'post' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo([
                        'refresh_token' => 'mySavedRefreshToken',
                        'grant_type' => 'refresh_token'
                    ]),
                ],
                'response' => [
                    'status' => 200,
                    'content' => [
                        'access_token' => 'accessToken',
                        'refresh_token' => 'refreshToken',
                        'expires_in' => 60
                    ]
                ]
            ]
        ]));

        $this->apiClient->setRefreshToken('mySavedRefreshToken');
        $this->apiClient->generateTokens();

        $this->assertEquals('accessToken', $this->apiClient->getAccessToken()->getToken());
        $this->assertEquals('refreshToken', $this->apiClient->getRefreshToken()->getToken());
    }

    public function testGenerateTokensWithRefreshTokenFails()
    {
        $this->expectException(TokenGenerationException::class);

        $this->apiClient->setBrowser($this->getBrowserMock([
            'post' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo([
                        'refresh_token' => 'mySavedRefreshToken',
                        'grant_type' => 'refresh_token'
                    ]),
                ],
                'response' => [
                    'status' => 400,
                    'content' => ''
                ]
            ]
        ]));

        $this->apiClient->setRefreshToken('mySavedRefreshToken');
        $this->apiClient->generateTokens();
    }

    public function testGet()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'get' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/shows?a=1&b=2&c%5Bd%5D=3'),
                    $this->equalTo([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ])
                ],
                'response' => [
                    'status' => 200,
                    'content' => ''
                ]
            ],

        ]));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->get('/shows', ['a' => 1, 'b' => 2, 'c' => ['d' => 3]], ['CustomHeader' => 'foo']);
    }

    public function testHead()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'head' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/shows?a=1&b=2&c%5Bd%5D=3'),
                    $this->equalTo([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ])
                ],
                'response' => [
                    'status' => 200,
                    'content' => ''
                ]
            ],

        ]));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->head('/shows', ['a' => 1, 'b' => 2, 'c' => ['d' => 3]], ['CustomHeader' => 'foo']);
    }

    public function testPatch()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'patch' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ]),
                    $this->equalTo('"content"')
                ],
                'response' => [
                    'status' => 200,
                    'content' => ''
                ]
            ],

        ]));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->patch('/shows/foo', 'content', ['CustomHeader' => 'foo']);
    }

    public function testPost()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'post' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ]),
                    $this->equalTo('"content"')
                ],
                'response' => [
                    'status' => 200,
                    'content' => ''
                ]
            ],

        ]));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->post('/shows/foo', 'content', ['CustomHeader' => 'foo']);
    }

    public function testDelete()
    {
        $this->apiClient->setBrowser($this->getBrowserMock([
            'delete' => [
                'expects' => $this->once(),
                'with' => [
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo([
                        'Content-Type' => 'application/json',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ])
                ],
                'response' => [
                    'status' => 200,
                    'content' => ''
                ]
            ],

        ]));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->delete('/shows/foo', ['CustomHeader' => 'foo']);
    }

    protected function getBrowserMock($data = [])
    {
        $browser = $this->getMockBuilder(Browser::class)
            ->onlyMethods(['head', 'get', 'post', 'patch', 'delete'])
            ->getMock();

        foreach($data as $method => $params) {
            $browser
                ->expects($params['expects'])
                ->method($method)
                ->withConsecutive($params['with'])
                ->willReturn($this->getResponseMock($params['response']['status'], $params['response']['content']));
        }

        return $browser;
    }

    protected function getResponseMock($status, $content)
    {
        $response = $this->getMockBuilder(Response::class)
            ->onlyMethods(['getStatusCode', 'getContent'])
            ->getMock();

        $response
            ->method('getStatusCode')
            ->willReturn($status);

        $response
            ->method('getContent')
            ->willReturn(json_encode($content, JSON_THROW_ON_ERROR));

        return $response;
    }
}
