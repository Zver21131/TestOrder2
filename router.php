<?php

$uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

if ($uri === '') {
    return;
}

$file = __DIR__ . '/' . $uri . '.php';

if (file_exists($file)) {
    require $file;
    exit;
} else {
    http_response_code(404);
    echo 'Page not found';
    exit;
}
