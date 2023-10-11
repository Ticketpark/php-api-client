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
$response = $client->get('/events/', ['maxResults' => 2]);

// 4. Handle the response
if ($response->isSuccessful()) {
    print "Request successful!\n\n";

    $events = $response->getContent();
    foreach($events as $event) {
        print $event['name']."\n";
    }
}

// 5. Recommended: Get the tokens and store them to use them again later on
$myAccessToken  = $client->getAccessToken();
$myRefreshToken = $client->getRefreshToken();