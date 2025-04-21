<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($uri === '/') {
    include 'home.php';
} elseif ($uri === '/about') {
    include 'about.php';
} elseif ($uri === '/contact') {
    include 'contact.php';
} else {
    http_response_code(404);
    echo '404 Not Found';
}
?>