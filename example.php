<?php

include('vendor/autoload.php');

// Create client
$client = new \Ticketpark\ApiClient\TicketparkApiClient('yourApiKey', 'yourApiSecret');

// You need a user to login
$client->setUserCredentials('some@username', 'somePassword');

// Execute the desired command
$response = $client->get('/events/');

//Handle the response
if ($response->isSuccessful()) {
    print "<strong>Request successful!</strong><br>";
    $events = json_decode($response->getContent(), true);

    foreach($events as $event) {
        print $event['name']."<br>";
    }
}