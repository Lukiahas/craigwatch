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

class Post {
	private $postDoc, $postXpath;

	public $url, $body, $replyTo, $updated, $posted;

	public function __construct($fullUrl) {
		$this->url = $fullUrl;
		$this->postDoc = new DomDocument(); 

		$this->postDoc->loadhtmlfile($this->url);
		libxml_clear_errors();
		$this->postXpath = new DomXpath($this->postDoc);

		if(!is_object($this->postXpath->query('//section[@class="userbody"]')->item(0))) {
			throw new Exception("Can't get listing");
		}
		else {
			$this->body = $this->getPostingBody();
			$this->replyTo = $this->getPostingReplyTo();
		}
		unset($this->postXpath);
	}

	private function getPostingBody() {
		return $this->postDoc->getElementById('postingbody')->nodeValue;
	}

	private function getPostingReplyTo() {
		return $this->postXpath->query('//section[@class="dateReplyBar"]')->item(0)->getElementsByTagName('a')->item(0)->nodeValue;
	}

	private function getPostingTime($whichDate = 'Posted') {
		$nodeNumber = 2;
		if($whichDate == 'Updated') {
			$nodeNumber = 3;
		}
		$postingInfo = $this->postXpath->query('//p[@class="postinginfo"]')->item($nodeNumber);
		echo $postingInfo->nodeValue."<br />\n";
		echo 'Interpreted time: '.strtotime(substr($postingInfo->nodeValue, (strlen($whichDate)+2)))."<br />\n";
		if(substr($postingInfo->nodeValue, 0, strlen($whichDate)) == $whichDate) {
			$postingDate = substr($postingInfo->getElementsByTagName('date')->item(0)->getAttribute('title'), 0, -3); //Craigslist has three too many digits on their timestamps, so we erase the last three.
			if(is_numeric($postingDate)) {
				$postingDate = $postingDate*1;
				if(is_int($postingDate) and $postingDate > 1300000000) {
					echo 'Server timezeone: '.date_default_timezone_get()."<br />\n";
					$dateTimeZoneServer = new DateTimeZone(date_default_timezone_get());
					$dateTimeZonePosting = new DateTimeZone(substr($postingInfo->nodeValue, -3));
					$dateTimePosting = new DateTime(substr($postingInfo->nodeValue, (strlen($whichDate)+2), -3), $dateTimeZonePosting);
					$timeOffset = $dateTimeZoneServer->getOffset($dateTimePosting);
					echo 'Timezone offset: '.$timeOffset."<br />\n";
					$adjusted = $postingDate+$timeOffset;
					echo 'Adjusted date: '.$adjusted."<br />\n";
					echo 'Adjusted string: '.date('Y-m-d, g:iA T', $adjusted)."<br />\n";
					return $postingDate;
				}
			}
		}
		return null;
	}
}