<?php
/** FALLBACK API **/
include('config.php');

// This file has been created with rush to meet the deadline of the #meleeton2017
//saveJsonRepositories();
//saveJsonRepositoriesCommits();
saveWordsToDb();

// Let's get the first 30 repositories
function saveJsonRepositories() {

	$urlJson = 'https://api.github.com/search/repositories?q=stars:%3E1&sort=stars?client_id=' . CLIENT_ID . '&client_secret=' . CLIENT_SECRET;
	$json = file_get_contents($urlJson);
	$jsonPath = 'data/repositories.json';
	file_put_contents($jsonPath, $json);
	palabrita('done');
}

// Let's get the first 30 commits for each repository
function saveJsonRepositoriesCommits() {

	$repositoriesJsonPath = 'data/repositories.json';
	$repositoriesJson = file_get_contents($repositoriesJsonPath);
	$repositories = json_decode($repositoriesJson, true);
	$reposJsonPathBase = 'data/repos/';

	foreach ($repositories['items'] as $index => $repository) {

		$position = $index + 1;
		$repoName = $repository['full_name'];

		$repoPath = $reposJsonPathBase . $position . '.json';
		palabrita($repoPath);
		if (!file_exists($repoPath)) {
			$url = 'https://api.github.com/repos/' . $repoName . '/commits?client_id=' . CLIENT_ID . '&client_secret=' . CLIENT_SECRET;
			palabrita($url);
			$json = get_url($url);
			palabrita($json);
			file_put_contents($repoPath, $json);
		}

	}
}

function saveWordsToDb() {

	$messages = array();
	$words = array();

	$reposBase = 'data/repos/';
	for ($i = 1; $i <= 30; $i++) {

		$repoCommitsJson = $reposBase . $i . '.json';
		$json = file_get_contents($repoCommitsJson);
		$commits = json_decode($json, true);

		foreach ($commits as $commit) {
			$messages[] = $commit['commit']['message'];
		}
	}

	foreach ($messages as $message) {

		$message = cleanMessage($message);
		$wordsMessage = explode(' ', $message);
		$words = array_merge($words, $wordsMessage);

	}

	$cleanWords = array();
	foreach ($words as $word) {
		$cleanWords[] = cleanWord($word);
	}

	$validWords = array();
	foreach ($cleanWords as $word) {
		if (isValidWord($word)) {
			$validWords[] = $word;
		}
	}

	$wordsOccurrences = array();
	foreach ($validWords as $word) {

		if (!isset($wordsOccurrences[$word])) {
			$wordsOccurrences[$word] = 0;
		} else {
			$wordsOccurrences[$word]++;
		}

	}


	arsort($wordsOccurrences);
	print_r(array_slice($wordsOccurrences, 0, 30));

}

function isValidWord($word) {

	$arrayNonValidWords = array('then', 'that', 'which', 'the', 'from', 'for', 'com', 'add', 'this');
	if (strlen(trim($word)) >= 3) {

		if (!in_array($word, $arrayNonValidWords)) {
			return true;
		}

	}

	return false;

}

function cleanWord($word) {

	return trim(strtolower($word));

}

function cleanMessage($message) {

	return preg_replace('/[^A-Za-z0-9\-]/', ' ', $message);

}

// AUXILIARS

// CURL
function get_url($url) {

	// Get cURL resource
	$curl = curl_init();
	// Set some options - we are passing in a useragent too here
	curl_setopt_array($curl, array(
		CURLOPT_RETURNTRANSFER => 1,
		CURLOPT_URL => $url,
		CURLOPT_USERAGENT => 'Codular Sample cURL Request'
	));
	// Send the request & save response to $resp
	$resp = curl_exec($curl);
	// Close request to clear up some resources
	curl_close($curl);

	return $resp;
}

// Log
function palabrita($message) {

	echo $message . PHP_EOL;
}