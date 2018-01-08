<?php

namespace rdx\fxwdns;

use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Cookie\SetCookie;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RedirectMiddleware;
use rdx\fxwdns\WebAuth;
use rdx\jsdom\Node;

class Client {

	public $base = 'https://cp.flexwebhosting.nl';
	public $auth; // rdx\fxwdns\WebAuth
	public $guzzle; // GuzzleHttp\Client
	public $customer; // rdx\fxwdns\Customer
	public $uri; // rdx\fxwdns\UriGen
	public $domains = [];

	/**
	 * Dependency constructor
	 */
	public function __construct( WebAuth $auth ) {
		$this->auth = $auth;
		$this->uri = new UriGen;

		$this->setUpGuzzle();
	}

	/**
	 *
	 */
	public function addDnsRecord( Domain $domain, DnsRecord $record ) {
		$name = preg_replace('#\.' . preg_quote($domain->name, '#') . '$#', '', $record->name);
		if ( $name == $record->name ) {
			return false;
		}

		$oldRecords = $this->getDnsRecords($domain);

		$rsp = $this->guzzle->request('POST', $this->uri->insertDnsRecord($domain), [
			'form_params' => [
				'name' => $name,
				'dnsNewType' => $record->type,
				'value' => $record->value,
				'prio' => $record->prio,
				'ttl' => $record->ttl,
				'objectId' => '',
				'dnsDomainId' => $domain->id,
				'coreDomainId' => '',
				'domainName' => $domain->name,
			],
		]);

		$html = (string) $rsp->getBody();
		$domain->records = $this->scrapeDnsRecords($html);

		return strpos($html, 'succesvol toegevoegd') !== false && count($oldRecords) == count($domain->records) - 1;
	}

	/**
	 *
	 */
	public function deleteDnsRecord( Domain $domain, DnsRecord $record ) {
		$rsp = $this->guzzle->request('GET', $this->uri->preDeleteDnsRecord($domain, $record));

		$rsp = $this->guzzle->request('POST', $this->uri->deleteDnsRecord($domain, $record), [
			'form_params' => [
				'objectId' => '',
				'dnsDomainId' => $domain->id,
				'dnsRecordId' => $record->id,
				'name' => $record->name,
				'value' => $record->value,
				'type' => $record->type,
				'prio' => $record->prio,
			],
		]);

		$oldRecords = $domain->records;

		$html = (string) $rsp->getBody();
		$domain->records = $this->scrapeDnsRecords($html);

		return strpos($html, 'succesvol verwijderd') !== false && count($oldRecords) == count($domain->records) + 1;
	}

	/**
	 *
	 */
	public function findDnsRecords( Domain $domain, array $conditions ) {
		$records = $domain->records ?: $this->getDnsRecords($domain);

		return array_filter($records, function(DnsRecord $record) use ($conditions) {
			foreach ( $conditions as $prop => $propValue ) {
				if ( $record->$prop != $propValue ) {
					return false;
				}
			}
			return true;
		});
	}

	/**
	 *
	 */
	public function getDnsRecords( Domain $domain ) {
		$rsp = $this->guzzle->request('GET', $this->uri->editDomainDns($domain));
		$html = (string) $rsp->getBody();

		return $domain->records = $this->scrapeDnsRecords($html);
	}

	/**
	 *
	 */
	protected function scrapeDnsRecords( $html ) {
		$doc = Node::create($html);
		$rows = $doc->queryAll('form#addNewDnsRecord tbody tr');

		$records = [];
		foreach ( $rows as $row ) {
			$td = $row->child('td:first-child');
			if ( !$td ) break;

			if ( !preg_match('#/dns/editShow(?:Direct)?/(\d+)/\d+#', $row->innerHTML, $match) ) break;

			$id = $match[1];
			$name = $td->textContent;

			$td = $td->nextElementSibling;
			$type = $td->textContent;

			$td = $td->nextElementSibling;
			$value = $td->textContent;
			if ( $type == 'TXT' ) {
				$value = trim($value, '"');
			}

			$td = $td->nextElementSibling;
			$prio = $td->textContent;

			$td = $td->nextElementSibling;
			$ttl = $td->textContent;

			$records[] = new DnsRecord($id, $name, $type, $value, $ttl, $prio);
		}

		return $records;
	}

	/**
	 *
	 */
	public function getDomain( $wanted ) {
		$domains = $this->domains ?: $this->getDomains();
		return array_reduce($domains, function($result, Domain $domain) use ($wanted) {
			return $domain->name === $wanted ? $domain : $result;
		}, null);
	}

	/**
	 *
	 */
	public function getDomains() {
		$rsp = $this->guzzle->request('GET', '/dns');
		$html = (string) $rsp->getBody();

		return $this->domains = $this->scrapeDomains($html);
	}

	/**
	 *
	 */
	protected function scrapeDomains( $html ) {
		$doc = Node::create($html);
		$rows = $doc->queryAll('.product tbody tr');

		$domains = [];
		foreach ( $rows as $row ) {
			if ( preg_match('#/dns/show(Direct)?/(\d+)#', $row->innerHTML, $match) ) {
				$direct = !!$match[1];
				$id = $match[2];
				$domain = $row->child(':first-child')->textContent;

				$domains[] = new Domain($id, $domain, $direct);
			}
		}

		return $domains;
	}

	/**
	 *
	 */
	public function logIn() {
		$rsp = $this->guzzle->request('GET', '/login');

		$rsp = $this->guzzle->request('POST', '/login/checkLogin', [
			'form_params' => [
				'email' => $this->auth->user,
				'password' => $this->auth->pass,
				'login' => 'Inloggen',
			],
		]);

		$html = (string) $rsp->getBody();
		if ( $rsp->getStatusCode() == 200 && strpos($html, '/login/logout') !== false ) {
			$doc = Node::create($html);
			$table = $doc->query('table.header');

			$id = $name = null;
			if ( preg_match('#^(.+?)Klantnummer(.+?)$#', $table->textContent, $match) ) {
				$name = trim($match[1]);
				$id = trim($match[2]);
			}
			$this->customer = new Customer($id, $name);

			return true;
		}

		return false;
	}

	/**
	 *
	 */
	protected function setUpGuzzle() {
		$cookies = $this->auth->cookies;
		$stack = HandlerStack::create();
		$this->guzzle = new Guzzle([
			'base_uri' => $this->base,
			'handler' => $stack,
			'cookies' => $cookies,
			'allow_redirects' => [
				'track_redirects' => true,
			] + RedirectMiddleware::$defaultSettings,
		]);

		$this->setUpLog($stack);
	}

	/**
	 *
	 */
	protected function setUpLog( HandlerStack $stack ) {
		$stack->push(Middleware::tap(
			function(Request $request, $options) {
				$this->guzzle->log[] = [
					'time' => microtime(1),
					'request' => strtoupper($request->getMethod()) . ' ' . $request->getUri(),
				];
			},
			function(Request $request, $options, PromiseInterface $response) {
				$response->then(function(Response $response) {
					$log = &$this->guzzle->log[count($this->guzzle->log) - 1];
					$log['time'] = microtime(1) - $log['time'];
					$log['response'] = $response->getStatusCode();
				});
			}
		));

		$this->guzzle->log = [];
	}

}
