<?php

require_once "Mailler.php";
class IMAPMailler implements Mailler{
	const DEFAULT_PORT = 143;

	function __construct($imapAuth) {
		$this->imapAuth = $imapAuth;
	}
	public function send($mail = array()){
		mb_language("ja");
		mb_internal_encoding("UTF-8");

		$mailto = $mail["to"];
		$mailfrom = $mail["from"];
		$subject = $mail["subject"];
		$body = $mail["body"];


		$host = $this->imapAuth["SERVER"];
		$port = isset($this->imapAuth["PORT"]) ?
				 $this->imapAuth["PORT"] : self::DEFAULT_PORT;
		$user = $this->imapAuth["USER"];
		$pass = $this->imapAuth["PASS"];
		
		$mbox = imap_open("{".$host.":".$port."/notls}Sent", $user, $pass);

		imap_append($mbox, "{".$host.":".$port."/notls}Sent",
			 "From: $mailfrom\r\n".
			 "To: $mailto\r\n".
			 "Subject: ".$subject."\r\n".
			 "Date: ".date("r", strtotime("now"))."\r\n".
			 "\r\n".
			 $body.
			 "\r\n"
		 );

		// close mail connection.
		imap_close($mbox);
	}
}