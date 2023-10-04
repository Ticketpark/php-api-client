<?php

include('vendor/autoload.php');

// 1. Create client
$client = new \Ticketpark\ApiClient\TicketparkApiClient('yourApiKey', 'yourApiSecret');

// 2a. Set your user credentials
$client->setUserCredentials('your@username.com', 'yourPassword');

// 2b. If possible, set one or both existing tokens
//     With frequent requests, re-using tokens results in less api requests than using user credentials only.
//
// $client->setAccessToken('someAccessTokenString');
// $client->setRefreshToken('someRefreshToken');

// 3. Execute the desired command
$response = $client->get('/events/', array('maxResults' => 2));

// 4. Handle the response
//    It is an instance of Buzz\Message\Response
if ($response->isSuccessful()) {
    print "<strong>Request successful!</strong><br>";
    $events = json_decode($response->getContent(), true);

    foreach($events as $event) {
        print $event['name']."<br>";
    }
}

// 5. Get the tokens and store them to use them again later on
$myAccessToken  = $client->getAccessToken();
$myRefreshToken = $client->getRefreshToken();