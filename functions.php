<?php

function openDatabaseConnection() {
	try {
		return new PDO("mysql:dbname=DATABASE;host=127.0.0.1", "USERNAME", "PASSWORD");
	}
	catch(PDOException $e) {
		echo $e->getMessage();
		exit;
	}
}

function checkEngineStatus(&$databaseConnection) {
	$query = 'SELECT status FROM enginestatus WHERE id=1;';
	$resultset = $databaseConnection->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$resultset->execute();
	return $resultset->fetch();
}

function getCurrentSeraches(&$databaseConnection) {
	$query = 'SELECT 
		watches.id, watches.email, watches.url, maxprice, watches.location, datePosted, description, watches.userid, pid, empty, sendfrom,
		users.email AS userEmail
	FROM
		watches
	JOIN
		users
	ON
		watches.userid = users.id
	WHERE 
		watches.deleted=0
	ORDER BY
		watches.id;';
	$resultset = $databaseConnection->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$resultset->execute();
	return $resultset;
}

function getSerach(&$databaseConnection, $searchId) {
	$query = 'SELECT 
		watches.id, watches.email, watches.url, maxprice, watches.location, datePosted, description, watches.userid, pid, empty, sendfrom,
		users.email AS userEmail
	FROM
		watches
	JOIN
		users
	ON
		watches.userid = users.id
	WHERE 
		watches.id = :searchId;';
	$resultset = $databaseConnection->prepare($query, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$resultset->execute(array(':searchId' => $searchId));
	return $resultset;
}

function disableEngine(&$databaseConnection, $reason) {
	$updateQuery = "UPDATE enginestatus SET status='down' WHERE id=1;";
	$update = $databaseConnection->prepare($updateQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$update->execute();
	echo "Engine disabled.<br />\n";
}