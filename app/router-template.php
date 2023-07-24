<?php
// Might be good to copy from the bottom of https://github.com/wp-cli/server-command/blob/main/router.php
define('ABSPATH', __DIR__ . '/');
if (preg_match('/\.(?:png|jpg|jpeg|gif|css|js|webp|aiff)$/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;    // serve the requested resource as-is.
} elseif (preg_match('/\.php$/', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    require ABSPATH . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
} elseif (file_exists(ABSPATH . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . 'index.php')) {
    require(ABSPATH . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) . 'index.php');
} else {
    require 'index.php';
}
