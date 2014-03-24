<?php

$worker = new GearmanWorker();
$worker->addServer();
$worker->addFunction("check_watch", "check_watch");
while($worker->work());

function check_watch(GearmanJob $job) {

	include_once('/absolute/path/to/includes.php');
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

	$workload = json_decode($job->workload());

	$search = new Watch();
	$search->id = $workload->id;
	$search->email = $workload->email;
	$search->url = $workload->url;
	$search->maxprice = $workload->maxprice;
	$search->location = $workload->location;
	$search->date = $workload->date;
	$search->description = $workload->description;
	$search->userid = $workload->userid;
	$search->replyto = $workload->replyto;
	$search->name = $workload->name;
	$search->pid = $workload->pid;
	$search->empty = $workload->empty;
	$search->userEmail = $workload->userEmail;
	$search->from = $workload->from;

	echo "\n\nSearch ".$search->id.': '.$search->url."\n";

	//Get first post information
	try {
		$doc = new DomDocument();
		@$doc->loadhtmlfile(urlencode($search->url));
	}
	catch(Exception $e) {
		echo "Couldn\'t load the watch url, moving on to the next watch\n";
		return 'error';
	}

	libxml_clear_errors();
	$xpath = new DomXpath($doc);
	$p = $xpath->query('//p[@class="row"]');
	$listingsCheckedThisRun = 0;

	for($itemNumber = 0; $itemNumber < MAX_EMAILS; $itemNumber++) {

		//If there's no more results, move on to the next search
		if(!is_object($p->item($itemNumber))) {
			if($itemNumber == 0) {
				echo "No results.";
				if(!$search->empty or !empty($search->description)) {
					echo "Noting that for the future and ";
					$search->markAsEmpty();
				}
				echo "moving on to the next search\n";
				$emptyListings++;
				break;
			}
			else {
				echo "No more results, moving on to the next search\n";
			}
			$noResults++;
			break;
		}

		$node = $p->item($itemNumber);
		echo 'Item Number: '.$itemNumber."\n";
		echo "Node: ".$node->nodeValue."\n";

		$listing = new Listing();
		$listingsChecked++;

		if($listing->incorrectResults($xpath)) {
			echo "Craigslist can't find any local results so it's showing nearby ones\n";
			$incorrectListing++;
			break;
		}

		$listing->setDate($xpath, $node);
		if($listing->date < $search->date) {
			echo "This date is less than the saved one, moving on to the next search\n";
			$oldListing++;
			break;
		}

		$listing->setPid($xpath, $node);
		if(empty($listing->pid) or $listing->pid == $search->pid) {
			echo "The pid is the same as the stored one, moving on to the next search\n";
			$noDifference++;
			break;
		}

		$listing->setTitle($xpath, $node);
		if(empty($listing->title) or $listing->title == $search->description) {
			echo "The description is the same as the stored one, moving on to the next search\n";
			$noDifference++;
			break;
		}

		$listing->setPrice($xpath, $node);
		if($search->maxprice != 'none' and isset($listing->price) and $listing->price > $search->maxprice) {
			echo "The price is too high, moving on to the next listing\n";
			$tooExpensive++;
			continue;
		}

		$listing->setLocation($xpath, $node);
		if($search->location != '' and $search->location != 'any' and strcasecmp($listing->location, $search->location) != 0) {
			echo "The item is out of area, moving on to the next listing\n";
			$outOfArea++;
			continue;
		}

		$listing->setPicture($xpath, $node);

		try {
			$post = new Post($listing->getFullURL($search->url, $xpath, $p, $itemNumber));
			$postChecked++;
		}
		catch(Exception $e) {
			echo "There's something wrong with the listing, move on to the next search\n";
			$brokenPost++;
			break;
		}

		//Notify the user
		if($search->notifyUserOfNewListing($post, $listing)) {
			echo "User emailed about new listing: ".$listing->title."\n";
			$sentEmails++;

			if($listingsCheckedThisRun == 0) {
				$search->updateRecord($listing, $post);
				echo "Database updated, moving on to the next listing\n";
				$listingsCheckedThisRun++;
			}
		}
		else {
			//NOTE: Handle this however you want
		}
	}
	include('/absolute/path/to/cleanup.php');
	return json_encode(array(
		'listingsChecked' => $listingsChecked,
		'incorrectListing' => $incorrectListing,
		'oldListing' => $oldListing,
		'noDifference' => $noDifference,
		'tooExpensive' => $tooExpensive,
		'outOfArea' => $outOfArea,
		'postChecked' => $postChecked,
		'brokenPost' => $brokenPost,
		'sentEmails' => $sentEmails,
		'errors' => $errors,
		'noResults' => $noResults,
		'emptyListings' => $emptyListings
	));
}