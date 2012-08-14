<?php
require_once "src/SendmailMailler.php";

$mailler = new SendmailMailler();
$mailler->send(array(
	"to" => "to@localhost",
	"from" => "from@localhost",
	"subject" => "テスト２",
	"body" => "本文です２"
));