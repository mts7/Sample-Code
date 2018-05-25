<?php
@session_start();

$timezone = 'America/Denver';
ini_set('date.timezone', $timezone);
date_default_timezone_set($timezone);

spl_autoload_register(function ($class) {
  // handle directories
  $dir = realpath(__DIR__) . DIRECTORY_SEPARATOR;
  $file = $dir . str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
  if (file_exists($file)) {
    require $file;
  }
});

$poll = new \Polls\Polls();

echo $poll->getHeader();
echo $poll->viewAll();
echo $poll->getFooter();
