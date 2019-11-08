<?php

require_once "phar://" . __DIR__ . "/PublicTransportInfo.phar/vendor/autoload.php";

$publicTransportInfo = new PublicTransportInfo\PublicTransportInfo(__DIR__ . "/config.php");

header("Content-Type: application/json; charset=utf-8");
echo json_encode($publicTransportInfo->getInfos());
