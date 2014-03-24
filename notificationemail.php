<?php

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