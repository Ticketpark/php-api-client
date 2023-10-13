# Ticketpark PHP API Client

[![Build Status](https://github.com/Ticketpark/php-api-client/actions/workflows/ci.yml/badge.svg)](https://github.com/sprain/php-swiss-qr-bill/actions)


A PHP client to consume the Ticketpark REST API.

## Installation

Add this library to your composer.json:

```
composer require ticketpark/php-api-client
```

## Usage

Also see `example.php`.

### Getting data (GET)

```php
<?php

include('vendor/autoload.php');

$client = new \Ticketpark\ApiClient\TicketparkApiClient('yourApiKey', 'yourApiSecret');
$client->setUserCredentials('your@username.com', 'yourPassword');

$response = $client->get('/events/', ['maxResults' => 2]);

if ($response->isSuccessful()) {
    $data = $response->getContent();
}
```

### Creating data (POST)

```php
<?php

include('vendor/autoload.php');

$client = new \Ticketpark\ApiClient\TicketparkApiClient('yourApiKey', 'yourApiSecret');
$client->setUserCredentials('your@username.com', 'yourPassword');

$response = $client->post('/events/', [
    'host' => 'yourHostPid',
    'name' => 'Some great event',
    'currency' => 'CHF'
]);

if ($response->isSuccessful()) {
    $pidOfNewEvent = $response->getGeneratedPid();
    
    // if you created a collection of records, the response will contain a link instead
    // that can be used to fetch the data of the newly generated records.
    //
    // $path = $response->getGeneratedListLink();
    // $newResponse = $client->get($path);
}
```

### Updating data (PATCH)

```php
<?php

include('vendor/autoload.php');

$client = new \Ticketpark\ApiClient\TicketparkApiClient('yourApiKey', 'yourApiSecret');
$client->setUserCredentials('your@username.com', 'yourPassword');

$response = $client->patch('/events/yourEventPid', [
    'name' => 'Some changed event name'
]

if ($response->isSuccessful()) {
    // Data was successfully updated
}
```


## API credentials
Get in touch to get your API credentials:<br>
[support@ticketpark.ch](mailto:support@ticketpark.ch)
