<?php
require_once "Mailler.php";
class AuthrorizedMailler implements Mailler{
	const DEFAULT_PORT = 587;
	const BUFFER_SIZE = 1024;
	const HELO = "HELO"; // or EHLO
	private $smtpAuth;

	function __construct($smtpAuth) {
		$this->smtpAuth = $smtpAuth;
	}

	public function send($mail = array()){
		//print_r($info);
		mb_language("ja");
		$subject = $mail["subject"];
		$body = $mail["body"];
		$host = $this->smtpAuth["SERVER"];
		$port = isset($this->smtpAuth["PORT"]) ?
				 $this->smtpAuth["PORT"] : self::DEFAULT_PORT;
		$user = base64_encode($this->smtpAuth["USER"]);
		$pass = base64_encode($this->smtpAuth["PASS"]);

		$subject = mb_convert_encoding($subject, "ISO-2022-JP","AUTO");
		$subject = mb_encode_mimeheader($subject);

		$conn = fsockopen($host, $port, $errno, $errstr, 30);
		
		if (!$conn) {
			throw new Exception("Error: $errstr ($errno)");
		}

		// Process start.
		$this->judgeRead($conn, "220");
		
		$this->conWrite($conn, self::HELO." ".$host);
		$this->judgeRead($conn, "250");

		$this->conWrite($conn, "MAIL FROM: ".$mail["from"]);
		$this->judgeRead($conn, "250");
		
		$this->conWrite($conn, "AUTH LOGIN");
		$this->judgeRead($conn, "334");
		$this->conWrite($conn, $user);
		$this->judgeRead($conn, "334");
		$this->conWrite($conn, $pass);
		$this->judgeRead($conn, "235");

		$this->conWrite($conn, "RCPT TO:".$mail["to"]);
		$this->conWrite($conn, "DATA");

		$this->judgeRead($conn, "250");

		$message = $this->conRead($conn);
		$this->conWrite($conn, "Subject: ".$subject);
		$this->conWrite($conn, "From: ".$mail["from"]);
		$this->conWrite($conn, "Content-Type: text/plain; charset=UTF-8; format=flowed");
		$this->conWrite($conn, "Content-Transfer-Encoding: 8bit");
		$body = "\r\n$body\r\n.";
		$this->conWrite($conn, $body);

		$this->judgeRead($conn, "250");
		$this->conWrite($conn, "QUIT");
		fclose($conn);
	}

	public function judgeRead($conn, $accept){
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if(!preg_match('/^'.$accept.'/', $res[0])) {
			throw new Exception("Error:\"$message\"");
		}
		return $message;
	}

	public function conWrite($conn, $str){
		echo ">>".$str.PHP_EOL;
		fwrite($conn, $str."\r\n");
	}
	public function conRead($conn) {
		$str = fgets($conn, self::BUFFER_SIZE);
		echo "<<".$str.PHP_EOL;
		return $str;
	}
}