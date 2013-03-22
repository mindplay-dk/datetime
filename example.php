<?php

header('Content-type: text/plain');

require_once __DIR__ . '/mindplay/datetime/DateTimeHelper.php';
require_once __DIR__ . '/mindplay/datetime/DateTimeConfig.php';
require_once __DIR__ . '/datetime.php';

datetime()->config->timezone = new DateTimeZone('EST');

echo datetime() . "\n";

$now = time();

echo datetime($now)->date()->long . "\n";
echo datetime($now)->datetime . "\n";
echo datetime($now)->short . "\n";
echo datetime($now)->long . "\n";
echo datetime($now)->utc() . "\n";
echo datetime($now)->timezone('EST') . "\n";
echo datetime($now)->timezone('PST') . "\n";
echo datetime($now)->date . "\n";
echo datetime($now)->time . "\n";
echo datetime($now)->format('time') . "\n";
echo datetime($now)->format('m.d.Y') . "\n";
echo datetime($now)->month()->add('1 month') . "\n";
echo datetime($now)->sub('20 minutes') . "\n";
