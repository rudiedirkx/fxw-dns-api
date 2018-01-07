<?php

use rdx\fxwdns;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/env.php';

$client = new fxwdns\Client(new fxwdns\WebAuth(FXW_USER, FXW_PASS));
var_dump($client->logIn());

echo "\n";

var_dump($client->customer);

echo "\n";

$domains = $client->getDomains();
// print_r($domains);

$domain = array_reduce($domains, function($result, fxwdns\Domain $domain) {
	return sha1($domain->name) == 'fa7bdb42018f749feb2f57551c88ec87b6f49362' ? $domain : $result;
}, null);
var_dump($domain);

if ( !$domain ) return;

$records = $client->getDnsRecords($domain);
// print_r($records);
// exit;

$delete = array_reduce($records, function($result, fxwdns\DnsRecord $record) {
	return preg_match('#^dns\d+\.#', $record->name) ? $record : $result;
}, null);
var_dump($delete);

if ( $delete ) {
	var_dump($client->deleteDnsRecord($domain, $delete));
}

$add = new fxwdns\DnsRecord(0, 'dns' . rand(101, 999) . '.' . $domain->name, 'txt', rand(), 300);
print_r($add);
var_dump($client->addDnsRecord($domain, $add));
