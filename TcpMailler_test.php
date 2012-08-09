<?php
require_once "src/TcpMailler.php";

$mailler = new TcpMailler();
$mailler->send(array(
	"to" => "to@localhost",
	"from" => "from@localhost",
	"subject" => "テスト２",
	"body" => "本文です２"
));