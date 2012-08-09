<?php
require_once "src/AuthrorizedMailler.php";

$mailler = new AuthrorizedMailler(array(
	"SERVER" => "hostname",
	"USER" => "user",
	"PASS" => "pass"
));
$mailler->send(array(
	"to" => "to@localhost",
	"from" => "from@localhost",
	"subject" => "テスト２",
	"body" => "This is test."
));