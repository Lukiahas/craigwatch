<?php

/************************************************************************
*  The Craigwatch engine was designed to periodically scan a website and
*  notify users of very specific changes.
*  Copyright (C) 2014  Beau Danger Lynn-Miller
*  
*  This program is free software: you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation, either version 3 of the License, or
*  (at your option) any later version.
*  
*  This program is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*  
*  You should have received a copy of the GNU General Public License
*  along with this program.  If not, see <http://www.gnu.org/licenses/>.
************************************************************************/

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