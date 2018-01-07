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
// print_r($domains);

$domain = array_reduce($domains, function($result, fxwdns\Domain $domain) {
	return sha1($domain->domain) == '3d899b0c0c7fd15567885b550898a51a557844d6' ? $domain : $result;
}, null);
var_dump($domain);

if ( !$domain ) return;

$records = $client->getDnsRecords($domain);
// print_r($records);

$record = array_reduce($records, function($result, fxwdns\DnsRecord $record) {
	return preg_match('#^dns\d+\.#', $record->name) ? $record : $result;
}, null);
var_dump($record);

if ( !$record ) return;

var_dump($client->deleteDnsRecord($domain, $record));
