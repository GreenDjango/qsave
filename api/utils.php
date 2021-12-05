<?php

function exit_with(int $response_code, $message)
{
	header("Content-Type: application/json; charset=UTF-8");
	http_response_code($response_code);
	if (is_string($message)) {
		exit(json_encode(
			array("message" => $message)
		));
	}
	exit(json_encode($message));
}

function clean_string(string $str) {
	return trim(htmlspecialchars(strip_tags($str)));
}

function parse_param_string(string $key) {
	if (array_key_exists($key, $_POST))
		$raw_value = $_POST[$key];
	elseif (array_key_exists($key, $_GET))
		$raw_value = $_GET[$key];

	if (!isset($raw_value))
		return false;
	if (!is_string($raw_value))
		exit_with(400, "'" . $key . "' parameter need to be a string.");
	$value = clean_string($raw_value);
	return $value;
}

function parse_param_number(string $key) {
	$value = parse_param_string($key);
	if (!$value) return false;
	if (!is_numeric($value))
		exit_with(400, "'" . $key . "' parameter need to be a number.");
	return floatval($value);
}

function clean_tags(string $str) {
	$str = trim($str, ";");
	$str = preg_replace('/;+/', ';', $str);
	if (preg_match('/[^a-z0-9-;]/', $str))
		exit_with(400, "'tags' parameter can only contain a-z or 0-9 or - or ; digit.");
	return $str;
}

function string_to_tags(string $str) {
	$clean_tags = [];

	if ($str) {
		$tags = explode(";", $str);
		foreach ($tags as $id => $tag) {
			if ($tag) array_push($clean_tags, $tag);
		}
	}
	return $clean_tags;
}

function retrieve_qnote(array $qnote) {
	$tags = [];
	if (array_key_exists("tags", $qnote))
		$tags = string_to_tags($qnote["tags"]);
	$qnote["tags"] = $tags;
	return $qnote;
}

function retrieve_qnotes(array $qnotes) {
	foreach ($qnotes as $id => $qnote) {
		$qnotes[$id] = retrieve_qnote((array) $qnote);
	}
	return $qnotes;
}