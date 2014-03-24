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

class NotificationEmail {
	public $subject, $message, $recipient, $sender;

	function setRecipient(&$databaseConnection, $watch) {
		if(strcasecmp($watch->email, $watch->userEmail) == 0) {
			$this->recipient = $watch->email;
		}
		else {
			$emailQuery = 'select validated, id, code, lastnotified from vmail where email=:email;';
			$emailParameters = array(':email' => $watch->email);

			$emailResultset = $databaseConnection->prepare($emailQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
			$emailResultset->execute($emailParameters);
			$email = $emailResultset->fetch();
			if(!empty($email['validated']) and $email['validated']==1) {
				$this->recipient = $watch->email;
			}
			else {
				echo "This email address hasn't been validated yet, using the users email. <br />\n";
				$this->recipient = $watch->userEmail;
				if(empty($email['code'])) {
					$this->recipient = ADMIN_EMAIL;
				}
				if((microtime(true) - strtotime($email['date'])) > TWO_WEEKS) {
					$message = 'You\'re receiving this email because you have a XXX search which is supposed to send email here, but you haven\'t validated the address yet. Until you validate this address, your XXX emails will go to your main account email. Click this link to validate this email address: craft.validation.address.com'."\r\n";
					$message = wordwrap($message, 75, "\r\n");
					$fromAddress = 'system@yourdomain.com';
					$headers = 'MIME-Version: 1.0' . "\r\n";
					$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
					$headers .= 'From: Email verification <'.$fromAddress.'>'."\r\n";
					$headers .= 'Reply-To: Email verification <'.$fromAddress.'>'."\r\n";
					$headers .= 'Return-Path: Email verification <'.$fromAddress.'>'."\r\n";
					mail($watch->email, "Email Validation", $message, $headers, '-f '.$fromAddress);
					$updateQuery = 'UPDATE vmail SET lastnotified = NOW( ) WHERE id = :id LIMIT 1;';
					$update = $databaseConnection->prepare($updateQuery, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
					$update->execute(array(
						':id' => $email['id']
					));
				}
				echo "Another verification email has been sent. <br />\n";
			}
		}
	}

	function setSender($post, $watch) {
		$this->sender = DEFAULT_SENDER;
		if($watch->from == 1 and $post->replyTo != 'see' and strpos($post->replyTo, '@') !== false) {
			$this->sender = $post->replyTo;
		}
	}
}