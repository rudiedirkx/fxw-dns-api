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

	/**
	 * Dependency constructor
	 */
	public function __construct( WebAuth $auth ) {
		$this->auth = $auth;

		$this->setUpGuzzle();
	}

	/**
	 *
	 */
	public function getDomains() {
		$rsp = $this->guzzle->request('GET', '/dns');
		$html = (string) $rsp->getBody();

		$doc = Node::create($html);
		$rows = $doc->queryAll('.product table tbody tr');
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
