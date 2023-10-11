# Ticketpark PHP API Client

A basic api client to consume the Ticketpark REST API.

## Installation

Simply add this library to your composer.json:

```
composer require ticketpark/php-api-client
```

## Usage

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


## User credentials
Get in touch with us to get your user credentials:
[tech@ticketpark.ch](mailto:tech@ticketpark.ch), [www.ticketpark.ch](http://www.ticketpark.ch)
