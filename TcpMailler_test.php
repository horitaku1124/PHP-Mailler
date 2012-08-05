<?php
require_once "src/TcpMailler.php";

$mail = new TcpMailler();
$mail->send(array(
	"to" => "to@localhost",
	"from" => "from@localhost",
	"subject" => "テスト２",
	"body" => "本文です２"
));