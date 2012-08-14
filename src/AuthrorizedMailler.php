<?php
define("AUTH_TYPE_LOGIN", 1);
define("AUTH_TYPE_CRAM_MD5", 2);

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
		mb_language("ja");
		$AuthType = AUTH_TYPE_LOGIN;

		$subject = $mail["subject"];
		$body = $mail["body"];
		$host = $this->smtpAuth["SERVER"];
		$port = isset($this->smtpAuth["PORT"]) ?
				 $this->smtpAuth["PORT"] : self::DEFAULT_PORT;

		$subject = mb_convert_encoding($subject, "ISO-2022-JP","AUTO");
		$subject = mb_encode_mimeheader($subject);

		$conn = fsockopen($host, $port, $errno, $errstr, 30);
		
		if (!$conn) {
			throw new Exception("Error: $errstr ($errno)");
		}

		// Process start.
		$this->judgeRead($conn, "220");
		
		$this->conWrite($conn, "EHLO ".$host);
		$message = $this->conRead($conn, true);
		$ehloInfo = $this->extractEHLOInfo($message);
		if(in_array("CRAM-MD5", $ehloInfo["AUTH+LOGIN"])) {
			$AuthType = AUTH_TYPE_CRAM_MD5;
		}
		//$this->judgeRead($conn, "250");

		$this->conWrite($conn, "MAIL FROM: ".$mail["from"]);
		$this->judgeRead($conn, "250");
		
		if($AuthType === AUTH_TYPE_LOGIN) {
			$this->authLogin($conn,
			 $this->smtpAuth["USER"], 
			 $this->smtpAuth["PASS"]);
		} else if($AuthType === AUTH_TYPE_CRAM_MD5) {
			$this->authCramMd5($conn,
			 $this->smtpAuth["USER"], 
			 $this->smtpAuth["PASS"]);
		}

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

	/**
	 * EHLOで受け付けるコマンドを確認
	 */
	public function extractEHLOInfo($str) {
		$lines = str_replace("\r\n", "\n", $str);
		$lines = explode("\n", $str);
		$info = array();
		foreach ($lines as $line) {
			if(preg_match('/SIZE (\d+)/', $line, $matches)) {
				$info["SIZE"] = $matches[1];
			} else if(preg_match('/AUTH LOGIN (.+)/', $line, $matches)) {
				$info["AUTH+LOGIN"] = explode(" ", $matches[1]);
			}
		}
		print_r($info);
		return $info;
	}
	public function judgeRead($conn, $accept){
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if(!preg_match('/^'.$accept.'/', $res[0])) {
			throw new Exception("Error:\"$message\"");
		}
		return $message;
	}

	/**
	 * ID、パスワードを平文送信でログイン
	 */
	public function authLogin($conn, $user, $pass) {
		$user = base64_encode($user);
		$pass = base64_encode($pass);
		$this->conWrite($conn, "AUTH LOGIN");
		$this->judgeRead($conn, "334");
		$this->conWrite($conn, $user);
		$this->judgeRead($conn, "334");
		$this->conWrite($conn, $pass);
		$this->judgeRead($conn, "235");
	}

	/**
	 * ID、パスワードをCRAM MD5でログイン
	 */
	public function authCramMd5($conn, $user, $pass) {
		$this->conWrite($conn, "AUTH CRAM-MD5");
		$line = $this->judgeRead($conn, "334");

		list(,$challenge) = explode(' ',$line);
		$challenge = base64_decode($challenge);
		$auth = base64_encode($user.' '.hash_hmac('md5', $challenge, $pass));

		$this->conWrite($conn, $auth);
		$this->judgeRead($conn, "235");
	}

	public function conWrite($conn, $str){
		echo ">>".$str.PHP_EOL;
		fwrite($conn, $str."\r\n");
	}
	public function conRead($conn, $continus = false) {
		if($continus) {
			// 本当は"250 xxxx"のハイフンなしが来るまで待つ模様
			$str = fread($conn, self::BUFFER_SIZE);
			echo "<<".$str.PHP_EOL;
		} else {
			$str = fgets($conn, self::BUFFER_SIZE);
			echo "<<".$str.PHP_EOL;
		}
		return $str;
	}
}