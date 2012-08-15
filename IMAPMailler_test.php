<?php
require_once "src/IMAPMailler.php";

$mailler = new IMAPMailler(array(
	"SERVER" => "server",
	"USER" => "user",
	"PASS" => "pass"
));
$mailler->send(array(
	"to" => "to@localhost",
	"from" => "from@localhost",
	"subject" => "test mail",
	"body" => "This is test."
));