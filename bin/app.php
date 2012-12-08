<?php
require __DIR__.'/../vendor/autoload.php';

$app = new \Cilex\Application('EmailFeed');
$app->command(new \Christiaan\EmailFeed\GenerateFeedCommand());
$app->run();
