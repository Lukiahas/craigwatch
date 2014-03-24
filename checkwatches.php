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

	include_once('/absolute/path/to/includes.php');

	$startTime = time();
	$searchChecked = 0;
	$listingsChecked = 0;
	$incorrectListing = 0;
	$oldListing = 0;
	$noDifference = 0;
	$tooExpensive = 0;
	$outOfArea = 0;
	$postChecked = 0;
	$brokenPost = 0;
	$sentEmails = 0;
	$emptyListings = 0;
	$errors = 0;
	$noResults = 0;

	$databaseConnection = openDatabaseConnection();

	$engineStatus = checkEngineStatus($databaseConnection);
	if($engineStatus['status'] !== 'up') {
		echo 'Engine is down, exiting.';
		exit;
	}

	//Go through current searches and check for new items
	//Get current searches
	$search = new Watch($databaseConnection);
	$currentSearches = getCurrentSeraches($databaseConnection);
	$currentSearches->setFetchMode(PDO::FETCH_INTO, $search);

	$client = new GearmanClient();
	$client->addServer();
	$client->setCompleteCallback("check_complete");

	while($search = $currentSearches->fetch()) {
		$client->addTask("check_watch", json_encode($search), null, $search->id);
	}

	$client->runTasks();

	function check_complete($result)
	{
		global $oldListing, $noDifference, $tooExpensive, $outOfArea, $errors, $noResults, $incorrectListing, $brokenPost, $sentEmails, $searchChecked, $listingsChecked, $emptyListings, $postChecked;
		echo $result->data()."\n";
		switch($result->data()) {
			case 'error':
				$errors++;
				break;
			default:
				$returnValues = json_decode($result->data());
				$listingsChecked += ($returnValues->listingsChecked*1);
				$incorrectListing += ($returnValues->incorrectListing*1);
				$oldListing += ($returnValues->oldListing*1);
				$noDifference += ($returnValues->noDifference*1);
				$tooExpensive += ($returnValues->tooExpensive*1);
				$outOfArea += ($returnValues->outOfArea*1);
				$postChecked += ($returnValues->postChecked*1);
				$brokenPost += ($returnValues->brokenPost*1);
				$sentEmails += ($returnValues->sentEmails*1);
				$errors += ($returnValues->errors*1);
				$noResults += ($returnValues->noResults*1);
				$emptyListings += ($returnValues->emptyListings*1);
				break;
		}
		$searchChecked++;
	}

	echo $sentEmails." emails sent.\n";
	$duration = time() - $startTime;
	echo 'The script took '.$duration." seconds to complete.\n";
	$tallyQuery = 'INSERT INTO emailtally (id, sent, runfinished, searcheschecked, listingschecked, incorrectListing, oldlistings, currentlistings, tooexpensive, outofarea, postschecked, brokenposts, emptylistings, duration) VALUES (NULL, :sentEmails, CURRENT_TIMESTAMP, :searcheschecked, :listingschecked, :incorrectListing, :oldlistings, :currentlistings, :tooexpensive, :outofarea, :postschecked, :brokenposts, :emptylistings, :duration);';
	$tally = $databaseConnection->prepare($tallyQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
	$tally->execute(array(
		':sentEmails' => $sentEmails,
		':searcheschecked' => $searchChecked,
		':listingschecked' => $listingsChecked,
		':incorrectListing' => $incorrectListing,
		':oldlistings' => $oldListing,
		':currentlistings' => $noDifference,
		':tooexpensive' => $tooExpensive,
		':outofarea' => $outOfArea,
		':postschecked' => $postChecked,
		':brokenposts' => $brokenPost,
		':emptylistings' => $emptyListings,
		':duration' => $duration
	));

	if($sentEmails > 1000) {
		echo "Too many emails sent, somethings suspicious...\n";
		disableEngine($databaseConnection, 'too many emails were sent');
	}
	
	include('/absolute/path/to/cleanup.php');

?>