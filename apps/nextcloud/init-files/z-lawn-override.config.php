<?php
$CONFIG = array (
  'dbhost' => getenv('POSTGRES_HOST'),
  'redis' => array (
    'host' => getenv('REDIS_HOST'),
    'password' => '',
    'port' => 6379,
  ),
);
