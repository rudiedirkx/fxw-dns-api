<?php

use rdx\fxwdns;

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/env.php';

$client = new fxwdns\Client(new fxwdns\WebAuth(FXW_USER, FXW_PASS));
var_dump($client->logIn());

echo "\n";

var_dump($client->customer);

echo "\n";

$domains = $client->getDomains();
print_r($domains);
