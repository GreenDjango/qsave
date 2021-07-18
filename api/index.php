<?php

declare(strict_types=1);

header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Credentials: true"); // need Allow-Origin != *
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, origin, accept, host, date, cookie, Access-Control-Allow-Headers, Authorization, X-Requested-With, API-key");

include_once('JSONDB.php');
include_once('utils.php');

use \Jajo\JSONDB;

define("STATS_DB", 'stats');
define("QNOTES_DB", 'qnotes');

$json_db = new JSONDB(__DIR__);

// if (!array_key_exists("HTTP_API_KEY", $_SERVER) || $_SERVER[""])

main();

function main()
{
	$get_routes = [
		'api/test' => 'get_test',
		'api/stats' => 'get_stats',
		'api/qnotes' => 'get_qnotes'
	];
	$post_routes = [
		'api/qnote' => 'add_qnote'
	];
	$uri = trim($_SERVER['DOCUMENT_URI'], "\t\n\r\0\x0B/");
	if (!$uri) $uri = "/";

	switch ($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			if (array_key_exists($uri, $get_routes)) {
				$get_routes[$uri]($uri);
				exit_with(200, "Success.");
			}
			exit_with(404, "Not Found.");
			break;
		case 'POST':
			if (array_key_exists($uri, $post_routes)) {
				$post_routes[$uri]($uri);
				exit_with(200, "Success.");
			}
			exit_with(404, "Not Found.");
			break;
		case 'OPTIONS':
			http_response_code(204);
			exit(0);
			break;
		default:
			break;
	}
	exit_with(405, "Method not allowed.");
}

function get_test()
{
	exit_with(200, array("server" => var_export($_SERVER, true)));
}

function get_stats()
{
	global $json_db;

	$stats = $json_db->select('*')
		->from(constant("STATS_DB"))
		->get();

	exit_with(200, array("stats" => $stats[0]));
}

function get_qnotes()
{
	global $json_db;

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->get();

	foreach ($qnotes as $id => $qnote) {
		// $qnote_arr = (array) $qnote;
		$qnote = parse_tags((array) $qnote);
		$qnotes[$id] = $qnote;
	}

	exit_with(200, array("qnotes" => $qnotes));
}

function add_qnote()
{
	global $json_db;
	$now = new DateTime();
	$new_qnote = [
		'id' => 1,
		'date' => $now->format(DateTime::ISO8601),
	];


	$tags_arg = parse_post_string("tags");
	if ($tags_arg) {
		$tags_arg = trim($tags_arg, ";");
		$tags_arg = preg_replace('/;+/', ';', $tags_arg);
		if (preg_match('/[^a-z0-9-;]/', $tags_arg))
			exit_with(400, "'tags' parameter can only contain a-z or 0-9 or - or ; digit.");
		if ($tags_arg) $new_qnote["tags"] = $tags_arg;
	}

	$url_arg = parse_post_string("url");
	$text_arg = parse_post_string("text");
	$code_arg = parse_post_string("code");
	if ($url_arg) $new_qnote["url"] = $url_arg;
	if ($text_arg) $new_qnote["text"] = $text_arg;
	if ($code_arg) $new_qnote["code"] = $code_arg;

	$result = $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->order_by("id", JSONDB::DESC)
		->limit(1)
		->get();
	if ($result) $new_qnote["id"] = $result[0]["id"] + 1;

	$json_db->insert(constant("QNOTES_DB"), $new_qnote);

	generate_stats();

	// exit_with(200, array("test" => $new_qnote));
}

function generate_stats() {
	global $json_db;
	$now = new DateTime();

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->order_by("date", JSONDB::DESC)
		->get();

	$all_tags = [];
	foreach ($qnotes as $id => $qnote) {
		//$qnote_arr = (array) $qnote;
		if (array_key_exists("tags", $qnote)) {
			$tags = explode(";", $qnote["tags"]);
			foreach ($tags as $id2 => $tag) {
				if (array_key_exists($tag, $all_tags)) $all_tags[$tag] += 1;
				else $all_tags[$tag] = 1;
			}
		}
	}

	
	$last_qnote = current($qnotes);
	if ($last_qnote) $last_qnote = parse_tags((array) $last_qnote);
	// if (array_key_exists(0, $qnotes) && array_key_exists("date" ,$qnotes[0]))
	// 	$last_qnote = $qnotes[0]["date"];

	$json_db->update([
			'total_qnotes' => count($qnotes),
			'last_qnote' => $last_qnote,
			'last_update' => $now->format(DateTime::ISO8601),
			'all_tags' => $all_tags,
			'db_size' => filesize(constant("QNOTES_DB") . ".json")
		])
		->from(constant("STATS_DB"))
		->where(['id' => 1])
		->trigger();
}

/*
$result = $this->db->select("*")
	->from("users")
	->where(array("age" => null, "name" => "Jajo"))
	->get();

$result = $this->db->select("*")
	->from("users")
	->where(array("age" => 50, "name" => "Jajo"), JSONDB::AND)
	->get();

$result = ($this->db->select("*")
	->from("users")
	->where(array(
		"state" => JSONDB::regex("/ria/"),
		"age" => JSONDB::regex("/5[0-9]/")
	), JSONDB::AND)
	->get());

$result = ($this->db->select("*")
	->from("users")
	->where(array("state" => JSONDB::regex("/Zam/")))
	->get());

$this->db->delete()
	->from('users')
	->where(['name' => 'Jammy'])
	->trigger();
*/
