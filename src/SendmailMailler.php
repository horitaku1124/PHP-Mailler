<?php

require_once "Mailler.php";
class SendmailMailler implements Mailler{
	function __construct() {}

	public function send($mail = array()){
		mb_language("ja");
		mb_internal_encoding("UTF-8");

		$mailto = $mail["to"];
		$subject = $mail["subject"];
		$content = $mail["body"];
		$mailfrom = "From:".$mail["from"];
		mb_send_mail($mailto, $subject, $content, $mailfrom);
	}
}