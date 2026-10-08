<?php
// Keep database connections and generated links correct when Lawn replaces containers.
$url = getenv('LAWN_URL') ?: 'http://localhost';
$address = parse_url($url);
$CONFIG = array (
  'dbhost' => getenv('POSTGRES_HOST'),
  'redis' => array (
    'host' => getenv('REDIS_HOST'),
    'password' => '',
    'port' => 6379,
  ),
  'overwrite.cli.url' => $url,
  'overwritehost' => $address['host'] . (isset($address['port']) ? ':' . $address['port'] : ''),
  'overwriteprotocol' => $address['scheme'],
);
