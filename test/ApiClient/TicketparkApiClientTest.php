<?php

namespace Ticketpark\ApiClient\Test;

use Ticketpark\ApiClient\TicketparkApiClient;
use Ticketpark\ApiClient\Token\AccessToken;
use Ticketpark\ApiClient\Token\RefreshToken;

class TicketparkApiClientTest extends \PHPUnit_Framework_TestCase
{
    protected $apiClient;

    public function setUp()
    {
        $this->apiClient = new TicketparkApiClient('apiKey', 'apiSecret');
    }

    public function testDefaultBrowser()
    {
        $this->assertInstanceOf('Buzz\Browser', $this->apiClient->getBrowser());
    }

    public function testSetValidBrowser()
    {
        $this->assertInstanceOf('Ticketpark\ApiClient\TicketparkApiClient', $this->apiClient->setBrowser($this->getBrowserMock()));
        $this->assertEquals('testBrowser', $this->apiClient->getBrowser()->getTestName());
    }

    public function testSetAccessToken()
    {
        $this->apiClient->setAccessToken('foo');
        $this->assertInstanceOf('Ticketpark\ApiClient\Token\AccessToken', $this->apiClient->getAccessToken());
        $this->assertEquals('foo', $this->apiClient->getAccessToken()->getToken());
    }

    public function testSetAccessTokenInstance()
    {
        $accessToken = new AccessToken();
        $accessToken->setToken('bar');
        $this->apiClient->setAccessTokenInstance($accessToken);

        $this->assertInstanceOf('Ticketpark\ApiClient\Token\AccessToken', $this->apiClient->getAccessToken());
        $this->assertEquals('bar', $this->apiClient->getAccessToken()->getToken());
    }

    public function testSetRefreshToken()
    {
        $this->apiClient->setRefreshToken('foo');
        $this->assertInstanceOf('Ticketpark\ApiClient\Token\RefreshToken', $this->apiClient->getRefreshToken());
        $this->assertEquals('foo', $this->apiClient->getRefreshToken()->getToken());
    }

    public function testSetRefreshTokenInstance()
    {
        $refreshToken = new RefreshToken();
        $refreshToken->setToken('bar');
        $this->apiClient->setRefreshTokenInstance($refreshToken);

        $this->assertInstanceOf('Ticketpark\ApiClient\Token\RefreshToken', $this->apiClient->getRefreshToken());
        $this->assertEquals('bar', $this->apiClient->getRefreshToken()->getToken());
    }

    /**
     * @expectedException  Ticketpark\ApiClient\Exception\TokenGenerationException
     */
    public function testGenerateTokensWithoutData()
    {
        $this->apiClient->generateTokens();
    }

    public function testGenerateTokensWithUserCredentials()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'post' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo(array(
                        'username' => 'username',
                        'password' => 'password',
                        'grant_type' => 'password'
                    )),
                ),
                'response' => array(
                    'status' => 200,
                    'content' => array(
                        'access_token' => 'accessToken',
                        'refresh_token' => 'refreshToken',
                        'expires_in' => 60
                    )
                )
            )
        )));

        $this->apiClient->setUserCredentials('username', 'password');
        $this->apiClient->generateTokens();

        $this->assertEquals('accessToken', $this->apiClient->getAccessToken()->getToken());
        $this->assertEquals('refreshToken', $this->apiClient->getRefreshToken()->getToken());
    }

    /**
     * @expectedException  Ticketpark\ApiClient\Exception\TokenGenerationException
     */
    public function testGenerateTokensWithUserCredentialsFails()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'post' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo(array(
                        'username' => 'username',
                        'password' => 'password',
                        'grant_type' => 'password'
                    )),
                ),
                'response' => array(
                    'status' => 400,
                    'content' => ''
                )
            )
        )));

        $this->apiClient->setUserCredentials('username', 'password');
        $this->apiClient->generateTokens();
    }

    public function testGenerateTokensWithRefreshToken()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'post' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo(array(
                        'refresh_token' => 'mySavedRefreshToken',
                        'grant_type' => 'refresh_token'
                    )),
                ),
                'response' => array(
                    'status' => 200,
                    'content' => array(
                        'access_token' => 'accessToken',
                        'refresh_token' => 'refreshToken',
                        'expires_in' => 60
                    )
                )
            )
        )));

        $this->apiClient->setRefreshToken('mySavedRefreshToken');
        $this->apiClient->generateTokens();

        $this->assertEquals('accessToken', $this->apiClient->getAccessToken()->getToken());
        $this->assertEquals('refreshToken', $this->apiClient->getRefreshToken()->getToken());
    }

    /**
     * @expectedException  Ticketpark\ApiClient\Exception\TokenGenerationException
     */
    public function testGenerateTokensWithRefreshTokenFails()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'post' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/oauth/v2/token'),
                    $this->anything(),
                    $this->equalTo(array(
                        'refresh_token' => 'mySavedRefreshToken',
                        'grant_type' => 'refresh_token'
                    )),
                ),
                'response' => array(
                    'status' => 400,
                    'content' => ''
                )
            )
        )));

        $this->apiClient->setRefreshToken('mySavedRefreshToken');
        $this->apiClient->generateTokens();
    }

    public function testGet()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'get' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/shows?a=1&b=2&c%5Bd%5D=3'),
                    $this->equalTo(array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ))
                ),
                'response' => array(
                    'status' => 200,
                    'content' => ''
                )
            ),

        )));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->get('/shows', array('a' => 1, 'b' => 2, 'c' => array('d' => 3)), array('CustomHeader' => 'foo'));
    }

    public function testHead()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'head' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/shows?a=1&b=2&c%5Bd%5D=3'),
                    $this->equalTo(array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ))
                ),
                'response' => array(
                    'status' => 200,
                    'content' => ''
                )
            ),

        )));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->head('/shows', array('a' => 1, 'b' => 2, 'c' => array('d' => 3)), array('CustomHeader' => 'foo'));
    }

    public function testPatch()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'patch' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo(array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    )),
                    $this->equalTo('content')
                ),
                'response' => array(
                    'status' => 200,
                    'content' => ''
                )
            ),

        )));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->patch('/shows/foo', 'content', array('CustomHeader' => 'foo'));
    }

    public function testPost()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'post' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo(array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    )),
                    $this->equalTo('content')
                ),
                'response' => array(
                    'status' => 200,
                    'content' => ''
                )
            ),

        )));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->post('/shows/foo', 'content', array('CustomHeader' => 'foo'));
    }

    public function testPut()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'put' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo(array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    )),
                    $this->equalTo('content')
                ),
                'response' => array(
                    'status' => 200,
                    'content' => ''
                )
            ),

        )));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->put('/shows/foo', 'content', array('CustomHeader' => 'foo'));
    }

    public function testDelete()
    {
        $this->apiClient->setBrowser($this->getBrowserMock(array(
            'delete' => array(
                'expects' => $this->once(),
                'with' => array(
                    $this->equalTo('https://api.ticketpark.ch/shows/foo'),
                    $this->equalTo(array(
                        'Content-Type' => 'application/x-www-form-urlencoded',
                        'Accept' => 'application/json',
                        'Authorization' => 'Bearer myAccessToken',
                        'CustomHeader' => 'foo'
                    ))
                ),
                'response' => array(
                    'status' => 200,
                    'content' => ''
                )
            ),

        )));

        $this->apiClient->setAccessToken('myAccessToken');
        $this->apiClient->delete('/shows/foo', array('CustomHeader' => 'foo'));
    }

    protected function getBrowserMock($data = array())
    {
        $browser = $this->getMockBuilder('Buzz\Browser')
            ->setMethods(array('getTestName', 'head', 'get', 'post', 'patch', 'put', 'delete'))
            ->getMock();

        $browser
            ->method('getTestName')
            ->willReturn('testBrowser');

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
        $response = $this->getMockBuilder('Buzz\Message\Response')
            ->setMethods(array('getStatusCode', 'getContent'))
            ->getMock();

        $response
            ->method('getStatusCode')
            ->willReturn($status);

        $response
            ->method('getContent')
            ->willReturn(json_encode($content));

        return $response;
    }
}