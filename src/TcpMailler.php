<?php
require_once "Mailler.php";
class TcpMailler implements Mailler{
	const DEFAULT_PORT = 25;
	const OUTBOUND_PORT = 587;
	const BUFFER_SIZE = 1024;
	public function parseMailAddress($address) {
		list($user, $domain) = explode("@", $address);
		$mailInfo = array("user" => $user, "domain" => $domain);
		
		$recordList = dns_get_record($domain, DNS_MX);
		if(count($recordList) == 0) throw new Exception("\"$domain\" has no MX record.");
		// MXレコードが複数あった場合はどうするか分からない。
		$mailInfo["target"] = $recordList[0]["target"];
		$recordList = dns_get_record($mailInfo["target"], DNS_A);
		if(isset($recordList[0]["ip"])) {
			$mailInfo["target_ip"] = $recordList[0]["ip"];
		}
		return $mailInfo;
	}
	public function send($mail = array()){
		$info = $this->parseMailAddress($mail["to"]);
		print_r($info);
		mb_language("ja");
		$subject = $mail["subject"];
		$body = $mail["body"];
		$host = isset($info["target_ip"]) ? $info["target_ip"] : $info["target"];
		$subject = mb_convert_encoding($subject, "ISO-2022-JP","AUTO");
		$subject = mb_encode_mimeheader($subject);

		$conn = fsockopen($host, self::DEFAULT_PORT, $errno, $errstr, 30);
		
		if (!$conn) {
			throw new Exception("Error: $errstr ($errno)");
		}
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if($res[0] != "220") {
			throw new Exception("Error:\"$message\"");
		}
		
		$this->conWrite($conn, "HELO ".$info["domain"]);
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if($res[0] != "250") {
			throw new Exception("Error:\"$message\"");
		}
		
		$this->conWrite($conn, "MAIL FROM: ".$mail["from"]);
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if($res[0] != "250") {
			throw new Exception("Error:\"$message\"");
		}
		
		$this->conWrite($conn, "RCPT TO:".$mail["to"]);
		$this->conWrite($conn, "DATA");
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if($res[0] != "250") {
			throw new Exception("Error:\"$message\"");
		}
		$message = $this->conRead($conn);
		$this->conWrite($conn, "Subject: ".$subject);
		$this->conWrite($conn, "From: ".$mail["from"]);
		$this->conWrite($conn, "Content-Type: text/plain; charset=UTF-8; format=flowed");
		$this->conWrite($conn, "Content-Transfer-Encoding: 8bit");
		$body = "\r\n$body\r\n.";
		$this->conWrite($conn, $body);
		$message = $this->conRead($conn);
		$res = explode(" ", $message);
		if($res[0] != "250") {
			throw new Exception("Error:\"$message\"");
		}
		$this->conWrite($conn, "QUIT");
		fclose($conn);
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