<?php
$CONFIG = array (
  'dbhost' => getenv('POSTGRES_HOST'),
  'redis' => array (
    'host' => getenv('REDIS_HOST'),
    'password' => '',
    'port' => 6379,
  ),
  'default_phone_region' => getenv('NEXTCLOUD_DEFAULT_PHONE_REGION') ?: 'US',
);
