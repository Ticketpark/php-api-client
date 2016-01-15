<?php

// RUN THIS EXAMPLE IN A BROWSER AS IT USES SESSIONS TO DEMONSTRATE THE BEHAVIOUR.

include('vendor/autoload.php');
session_start();

// Create client
$client = new \Ticketpark\ApiClient\TicketparkApiClient(
    'yourApiKey',
    'yourApiSecret'
);

// If you don't have any tokens yet, generate them with user credentials
if (!isset($_SESSION['accessToken'])) {

    // Set user credentials
    $client->setUserCredentials('some@username', 'somePassword');

    // Generate tokens
    $client->generateTokens();

    // Get access token and refresh token and save them locally in your app
    // (the example saves them in the session. You might want to save them in a database)
    $_SESSION['accessToken']  = $client->getAccessToken();
    $_SESSION['refreshToken'] = $client->getRefreshToken();

    print "<strong>Created token with user credentials.</strong><br>";
}

// If the tokens already exist or have been generated,
// use them instead of user credentials
$client->setAccessToken($_SESSION['accessToken']);
$client->setRefreshToken($_SESSION['refreshToken']);

// Execute the desired command
$response = $client->get('/events/');
#$response = $client->post('/events/',  array('host' => 'ABC…', 'name' => 'My event name'));
#$response = $client->patch('/events/DEF…', array('name' => 'My new event name'));

//Handle the response
if ($response->isSuccessful()) {
    print "<strong>Request successful!</strong><br>";
    $events = json_decode($response->getContent(), true);
    foreach($events as $event) {
        print $event['name']."<br>";
    }
}

// The tokens might have been renewed. Save them again.
$_SESSION['accessToken']  = $client->getAccessToken();
$_SESSION['refreshToken'] = $client->getRefreshToken();