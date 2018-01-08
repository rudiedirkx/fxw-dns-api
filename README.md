FXW DNS API
====

Manages FXW DNS records, by scraping their control panel, since they don't have an API.

	$client = new rdx\fxwdns\Client(new rdx\fxwdns\WebAuth('mail@address.com', 'p@assword'));

	// Explicit log in
	$bool = $client->logIn();

	// $client->customer->{id,name} contains some customer info from dashboard

	// Get managed domains
	$domains = $client->getDomains();

	// Get specific domain
	$domain = $client->getDomain('example.com');

	// Get domain's DNS records
	$records = $client->getDnsRecords($domain);

	// Add DNS record
	$record = new rdx\fxwdns\DnsRecord(0, 'sub.example.com', 'A', '12.34.56.78', 3600);
	$bool = $client->addDnsRecord($domain, $record);

	// Find specific DNS record(s)
	$records = $client->findDnsRecords(['type' => 'TXT', 'name' => 'sup.example.com']);

	// Remove DNS record
	$bool = $client->deleteDnsRecord($domain, $records[0]);
