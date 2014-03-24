<?php

class Listing {
	public $date, $title, $price, $location, $picture, $pid;

	function incorrectResults(&$xpath) {
		if(is_object($xpath->query('//h4[@class="ban"]')->item(0))) {
			$ban = $xpath->query('//h4[@class="ban"]')->item(0)->nodeValue;
			if($ban == 'Zero LOCAL results found. Here are some from NEARBY areas...') {
				return true;
			}
		}
		return false;
	}

	function setDate(&$xpath, &$node) {
		$dateString = $xpath->query('.//span[@class="date"]', $node)->item(0)->nodeValue;
		$when = strtotime($dateString.' '.date('Y'));
		$this->date = $when;
		$currentTime = microtime(true);
		if($when > $currentTime) {	//If we should have used last year instead of this year...
			$this->date = strtotime($dateString.' '.(date('Y')-1));
			echo "Issue getting proper posting date. Setting date again using last year.<br/>\n";
		}
		if($when > $currentTime) {	//If there was an issue getting the date resulting in it picking a future date...
			$this->date = $currentTime; //Set the date posted to today.
			echo "Issue getting proper posting date. Setting date for today.<br/>\n";
		}
		echo "Date: ".$this->date." (".date('M j, Y', $this->date).")<br />\n";
	}

	function setTitle(&$xpath, &$node) {
		$this->title = substr($node->getElementsByTagName('a')->item(1)->nodeValue, 0, 254);
		echo "Title: ".$this->title."<br />\n";
	}

	function setPrice(&$xpath, &$node) {
		$locationOfLastDollarSign = strrpos($node->nodeValue, '$');
		if($locationOfLastDollarSign === false) {
			echo "No price listed<br />\n";
			unset($this->price);
		}
		else {
			$firstSpaceAfterDollarSign = strpos($node->nodeValue, ' ', $locationOfLastDollarSign);
			$priceLength = $firstSpaceAfterDollarSign-$locationOfLastDollarSign;
			$price = substr($node->nodeValue, $locationOfLastDollarSign+1, $priceLength)*1;
			if(is_numeric($price)) {
				$this->price = $price;
				echo "Price used: ".$this->price."<br />\n";
			}
		}
		if(!isset($this->price) or empty($this->price)) {
			if(is_object($xpath->query('.//span[@class="price"]', $node)->item(0))) {
				$price = substr($xpath->query('.//span[@class="price"]', $node)->item(0)->nodeValue, 1)*1;
				echo "Listed price: ".$xpath->query('.//span[@class="price"]', $node)->item(0)->nodeValue."<br />\n";
				if(!empty($price)) {
					$this->price = $price;
					echo "Price used: ".$this->price."<br />\n";
				}
			}
			else {
				echo "No price listed<br />\n";
				unset($this->price);
			}
		}
	}

	function setLocation(&$xpath, &$node) {
		if(is_object($node->getElementsByTagName('small')->item(0))) {
			$this->location = substr($node->getElementsByTagName('small')->item(0)->nodeValue, 2, -1);
		}
		else {
			echo "No location listed, using an empty string<br />\n";
			$this->location = '';
		}
	}

	function setPicture(&$xpath, &$node) {
		$this->picture = trim($xpath->query('.//span[@class="p"]', $node)->item(0)->nodeValue);
	}

	function setPid(&$xpath, &$node) {
		$this->pid = $node->getAttribute('data-pid');
		if($this->pid == 1 or $this->pid == "1") {
			$this->pid = null;
		}
		echo "PID: ".$this->pid."<br />\n";
	}

	function hasPicture() {
		if($this->picture == 'pic') {
			return true;
		}
		return false;
	}

	function getFullURL($url, &$xpath, &$p, $itemNumber) {
		$domain = substr($url, 0, strpos($url, '/', 8));
		$ahref = $p->item($itemNumber)->getElementsByTagName('a')->item(0)->getAttribute("href");
		if(substr($ahref, 0, 4) == 'http') {
			return $ahref;
		}
		else {
			return $domain.$ahref;
		}
	}
}