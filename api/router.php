<?php

declare(strict_types=1);

class Router
{
	const GET = "GET";
	const POST = "POST";
	const PUT = "PUT";
	const DELETE = "DELETE";

	public $uri_prefix = null;
	private $routes = [];
	private $auth_key;

	public function __construct(string $uri_prefix = null)
	{
		$this->uri_prefix = $uri_prefix;
		$this->auth_key = "";
	}

	public function add_route(string $method, string $path, string $callback, bool $need_auth = false)
	{
		if (!key_exists($method, $this->routes))
			$this->routes[$method] = [];
		if ($this->uri_prefix)
			$path = $this->uri_prefix . $path;
		$this->routes[$method][$path] = ['callback' => $callback, 'need_auth' => $need_auth];
	}

	public function call_request_route()
	{
		$parse_uri = null;
		if (key_exists("DOCUMENT_URI", $_SERVER)) {
			$parse_uri = "/" . trim($_SERVER['DOCUMENT_URI'], "\t\n\r\0\x0B/");
		} else if (key_exists("SCRIPT_URL", $_SERVER)) {
			$parse_uri = "/" . trim($_SERVER['SCRIPT_URL'], "\t\n\r\0\x0B/");
		}
		if (!$parse_uri) exit_with(500, "Missing URI.");

		$this->call_route($_SERVER['REQUEST_METHOD'], $parse_uri);
	}

	public function call_route(string $method, string $uri)
	{
		if ($method === 'OPTIONS') {
			http_response_code(204);
			exit(0);
		}

		if (!key_exists($method, $this->routes))
			exit_with(405, "Method not allowed.");

		if (!key_exists($uri, $this->routes[$method]))
			exit_with(404, "Not Found.");

		$route = $this->routes[$method][$uri];
		
		if (key_exists('need_auth', $route) && $route['need_auth']) {
			if (!key_exists("HTTP_API_KEY", $_SERVER) || $_SERVER["HTTP_API_KEY"] !== $this->auth_key)
				exit_with(401, "Unauthorized.");
		}

		$route['callback']($uri);
	}
}
