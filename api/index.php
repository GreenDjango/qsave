<?php

declare(strict_types=1);
if (false) {
	ini_set('display_startup_errors', 'On');
	ini_set('display_errors', 'On');
	error_reporting(-1);
}

http_response_code(500);
header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Credentials: true"); // need Allow-Origin != *
header("Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, origin, accept, host, date, cookie, Access-Control-Allow-Headers, Authorization, X-Requested-With, API-key");

include_once('JSONDB.php');
include_once('router.php');
include_once('utils.php');

use \Jajo\JSONDB;

define("STATS_DB", 'stats');
define("QNOTES_DB", 'qnotes');
define("RES_MAX", 50);
// define("URL_PREFIX", '/projects/qsave/api'); // TODO: use .env var
define("URL_PREFIX", '/api');
$json_db = new JSONDB(__DIR__);

main();

function main()
{
	$router = new Router(constant("URL_PREFIX"));

	// $router->add_route(Router::GET, '/test', 'get_test');
	$router->add_route(Router::GET, '/stats', 'get_stats');
	$router->add_route(Router::GET, '/tags', 'get_tags');
	$router->add_route(Router::GET, '/qnote', 'get_qnote');
	$router->add_route(Router::GET, '/qnotes', 'get_qnotes');
	$router->add_route(Router::GET, '/search', 'get_search');

	$router->add_route(Router::POST, '/qnote/create', 'add_qnote', true);
	$router->add_route(Router::POST, '/qnote/update', 'update_qnote', true);

	$router->add_route(Router::DELETE, '/qnote', 'delete_qnote', true);

	$router->call_request_route();

	exit_with(200, "Success.");
}

function get_test()
{
	exit_with(200, array("server" => var_export($_SERVER, true)));
}

function get_tags()
{
	global $json_db;

	$stats = $json_db->select('*')
		->from(constant("STATS_DB"))
		->get();

	exit_with(200, array("tags" => $stats[0]->all_tags));
}

function get_stats()
{
	global $json_db;

	$stats = $json_db->select('*')
		->from(constant("STATS_DB"))
		->get();

	exit_with(200, array("stats" => $stats[0]));
}

function get_qnote()
{
	global $json_db;

	$id_arg = parse_param_number("qnoteID");
	if (!$id_arg)
		exit_with(400, "'qnoteID' parameter are need.");
	$id_arg = (int)$id_arg;

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->where(['id' => $id_arg])
		->get();

	$qnote = array_shift($qnotes);
	if ($qnote)
		$qnote = retrieve_qnote($qnote);

	exit_with(200, array("qnote" => $qnote));
}

function get_qnotes()
{
	global $json_db;

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->limit(constant("RES_MAX"))
		->get();

	$qnotes = retrieve_qnotes($qnotes);

	exit_with(200, array("qnotes" => $qnotes));
}

function get_search()
{
	global $json_db;
	global $q_arg;
	$where = [];

	$tags_arg = parse_param_string("tags");
	if ($tags_arg) $tags_arg = clean_tags($tags_arg);
	if ($tags_arg) {
		$tagExp = [];
		$tags = string_to_tags($tags_arg);
		foreach ($tags as $id => $tag) {
			$tag = preg_quote($tag);
			array_push($tagExp, "(;" . $tag . ";)|(^" . $tag . ";)|(;" . $tag . "$)");
		}
		$reg = "/" . implode("|", $tagExp) . "/i";
		$where["tags"] = JSONDB::regex($reg);
	}

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->where($where, JSONDB::OR)
		->get();

	$q_arg = parse_param_string("q");
	if ($q_arg) {
		$qnotes = array_filter($qnotes, function ($qnote) {
			global $q_arg;
			$qnote = (array) $qnote;
			$keys = ["url", "text", "code"];
			foreach ($keys as $key) {
				$reg = "/" . preg_quote($q_arg, '/') . "/i";
				if (key_exists($key, $qnote) && preg_match($reg, $qnote[$key])) return true;
			}
			return false;
		});
	}

	$qnotes = array_slice($qnotes, 0, constant("RES_MAX"), false);
	$qnotes = retrieve_qnotes($qnotes);

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

	$tags_arg = parse_param_string("tags");
	if ($tags_arg) {
		$tags_arg = clean_tags($tags_arg);
		if ($tags_arg) $new_qnote["tags"] = $tags_arg;
	}

	$url_arg = parse_param_string("url");
	$text_arg = parse_param_string("text");
	$code_arg = parse_param_string("code");
	$code_lang_arg = parse_param_string("code_lang");
	if ($url_arg) $new_qnote["url"] = $url_arg;
	if ($text_arg) $new_qnote["text"] = $text_arg;
	if ($code_arg) {
		$new_qnote["code"] = $code_arg;
		$new_qnote["code_lang"] = "plaintext";
	}
	if ($code_lang_arg) $new_qnote["code_lang"] = $code_lang_arg;

	if ((!$url_arg || !strlen($url_arg)) && (!$text_arg || !strlen($text_arg)) && (!$code_arg || !strlen($code_arg)))
		exit_with(400, "'url' or 'text' or 'code' parameters are need.");

	$result = $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->order_by("id", JSONDB::DESC)
		->limit(1)
		->get();
	if ($result) $new_qnote["id"] = $result[0]["id"] + 1;

	$json_db->insert(constant("QNOTES_DB"), $new_qnote);

	generate_stats();

	exit_with(201, "Created.");
}

function update_qnote()
{
	global $json_db;

	$id_arg = parse_param_number("qnoteID");
	if (!$id_arg)
		exit_with(400, "'qnoteID' parameter are need.");
	$id_arg = (int)$id_arg;

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->where(['id' => $id_arg])
		->get();

	$qnote = current($qnotes);
	if (!$qnote)
		exit_with(404, "Qnote not found.");

	$tags_arg = parse_param_string("tags");
	if ($tags_arg !== false) {
		$tags_arg = clean_tags($tags_arg);
		$qnote["tags"] = $tags_arg;
	}

	$url_arg = parse_param_string("url");
	$text_arg = parse_param_string("text");
	$code_arg = parse_param_string("code");
	$code_lang_arg = parse_param_string("code_lang");

	if ($url_arg !== false) $qnote["url"] = $url_arg;
	if ($text_arg !== false) $qnote["text"] = $text_arg;
	if ($code_arg !== false) {
		$qnote["code"] = $code_arg;
		$qnote["code_lang"] = "plaintext";
	}
	if ($code_lang_arg) $qnote["code_lang"] = $code_lang_arg;

	if (!$qnote["url"] && !$qnote["text"] && !$qnote["code"])
		exit_with(400, "'url' or 'text' or 'code' parameters are need.");

	$json_db->update($qnote)
		->from(constant("QNOTES_DB"))
		->where(['id' => $id_arg])
		->trigger();

	generate_stats();

	exit_with(200, "Updated.");
}

function delete_qnote()
{
	global $json_db;

	$id_arg = parse_param_number("qnoteID");
	if (!$id_arg)
		exit_with(400, "'qnoteID' parameter are need.");
	$id_arg = (int)$id_arg;

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->where(['id' => $id_arg])
		->get();

	$qnote = current($qnotes);
	if (!$qnote)
		exit_with(404, "Qnote not found.");

	$json_db->delete()
		->from(constant("QNOTES_DB"))
		->where(['id' => $id_arg])
		->trigger();

	generate_stats();

	exit_with(200, "Deleted.");
}

function generate_stats()
{
	global $json_db;
	$now = new DateTime();

	$qnotes = (array) $json_db->select('*')
		->from(constant("QNOTES_DB"))
		->order_by("date", JSONDB::DESC)
		->get();

	$all_tags = [];
	foreach ($qnotes as $id => $qnote) {
		//$qnote_arr = (array) $qnote;
		if (key_exists("tags", $qnote)) {
			$tags = string_to_tags($qnote["tags"]);
			foreach ($tags as $id2 => $tag) {
				if (key_exists($tag, $all_tags)) $all_tags[$tag] += 1;
				else $all_tags[$tag] = 1;
			}
		}
	}
	array_multisort($all_tags);

	$older_qnote = end($qnotes);
	if ($older_qnote) $older_qnote = retrieve_qnote((array) $older_qnote);
	else $older_qnote = null;
	reset($qnotes);

	$last_qnote = current($qnotes);
	if ($last_qnote) $last_qnote = retrieve_qnote((array) $last_qnote);
	else $last_qnote = null;

	$json_db->update([
		'total_qnotes' => count($qnotes),
		'last_qnote' => $last_qnote,
		'older_qnote' => $older_qnote,
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
