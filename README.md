FXW DNS API
====

Manages FXW DNS records, by scraping their control panel, since they don't have an API.

	$client = new rdx\fxwdns\Client(new rdx\fxwdns\WebAuth('mail@address.com', 'p@assword'));

	// Explicit log in
	$client->logIn();

	// $client->customer contains some customer info from dashboard: id & name

	// Get managed domains
	$domains = $client->getDomains();
