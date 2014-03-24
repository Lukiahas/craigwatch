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

class Watch {
	private $databaseConnection;

	public $id, $email, $url, $maxprice, $location, $date, $description, $userid, $replyto, $name, $pid;

	function checkForUpdate() {
		return true;
	}

	function notifyUserOfNewListing(&$post, &$listing) {
		$email = new NotificationEmail();
		$email->subject = $listing->title;
		if(isset($listing->price)) {
			$email->subject .= ' $'.$listing->price;
		}
		$email->message = '';
		if($listing->hasPicture()) {
			$email->message .= "This post contains images.\r\n\r\n";
		}

		$email->message .= $post->body."\r\n Go to the product page here: ".$post->url."\r\n\r\n\r\nTo remove this item from your watch list, click here: http://".DOMAIN_NAME."/index.php?id=".$this->id."&userid=".$this->userid;
		
		$this->databaseConnection = openDatabaseConnection();
		$email->setRecipient($this->databaseConnection, $this);

		$email->setSender($post, $this);

		$this->setName();

		$headers = 'MIME-Version: 1.0' . "\r\n";
		$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
		$headers .= 'From: '.$this->name.' search <'.$email->sender.'>'."\r\n";
		$headers .= 'Reply-To: '.$this->name.' search <'.$post->replyTo.'>'."\r\n";
		$headers .= 'Return-Path: '.$this->name.' search <'.$email->sender.'>'."\r\n";
		if(mail($email->recipient, $email->subject, $email->message, $headers, '-f '.$email->sender)) {
			return true;
		}
		return false;
	}

	function setName() {
		$this->name = substr($this->url, (strpos($this->url, '?query=')+7));
		if(strpos($this->name, '&')){
			$this->name = substr($this->name,0,strpos($this->name, '&'));
		}
	}

	function updateRecord(&$listing, &$post) {
		try {
			$updateQuery = 'UPDATE watches SET description = :description, pid = :pid, datePosted = :date WHERE id = :id LIMIT 1;';
			$update = $this->databaseConnection->prepare($updateQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$updateResult = $update->execute(array(
				':description' => $listing->title,
				':pid' => $listing->pid,
				':date' => $listing->date,
				':id' => $this->id
			));
			if($updateResult !== TRUE or $update->rowCount > 1) {
				echo "Unable to update the database<br />\n";
				disableEngine($this->databaseConnection, 'the database update failed');
				print_r($updateResult);
				exit;
			}
		}
		catch(PDOException $e) {
			echo "Unable to update the database: ".$e->getMessage()."<br />\n";
			disableEngine($this->databaseConnection, 'the database update failed ('.$e->getMessage().')');
			print_r($e);
			exit;
		}
	}

	function markAsEmpty() {
		$this->databaseConnection = openDatabaseConnection();
		try {
			$updateQuery = 'UPDATE watches SET datePosted = \'0\', description = \'\', pid = NULL, empty = \'1\' WHERE id =:id LIMIT 1;';
			$update = $this->databaseConnection->prepare($updateQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$updateResult = $update->execute(array(
				':id' => $this->id
			));
			if($updateResult !== TRUE) {
				echo "Unable to update the database<br />\n";
				disableEngine($this->databaseConnection, 'the database update failed');
				print_r($updateResult);
				exit;
			}
		}
		catch(PDOException $e) {
			echo "Unable to update the database: ".$e->getMessage()."<br />\n";
			disableEngine($this->databaseConnection, 'the database update failed ('.$e->getMessage().')');
			print_r($e);
			exit;
		}
	}
}