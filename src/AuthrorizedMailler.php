<?php
require_once "Mailler.php";
class AuthrorizedMailler implements Mailler{
	const DEFAULT_PORT = 587;
	const BUFFER_SIZE = 1024;
	public function send($mail = array()){
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