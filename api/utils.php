<?php

function exit_with(int $response_code, mixed $message)
{
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

function parse_post_string(string $key) {
	if (!array_key_exists($key, $_POST)) return false;
	$raw_value = $_POST[$key];
	if (!is_string($raw_value))
		exit_with(400, "'" . $key . "' parameter need to be a string.");
	$value = clean_string($raw_value);
	if (!$value) return false;
	return $value;
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

function parse_tags(array $qnote) {
	$tags = [];
	if (array_key_exists("tags", $qnote))
		$tags = string_to_tags($qnote["tags"]);
	$qnote["tags"] = $tags;
	return $qnote;
}