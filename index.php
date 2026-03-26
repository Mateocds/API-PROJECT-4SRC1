<?php
header("Content-Type: application/json");

require 'metriques.php';

function send($data, $code = 200) {
    http_response_code($code);
    echo json_encode($data);
    exit;
}

function send_error($message, $code) {
    send([
        "error"      => $message,
        "status"     => $code,
        "checked_at" => fmt_date()
    ], $code);
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = str_replace('/api/v1', '', $uri);

match($uri) {
    '/health' => send(get_health()),
    '/cpu'    => send(get_cpu()),
    '/memory' => send(get_memory()),
    '/disk'   => send(get_disk()),
    '/all'    => send(get_all()),
    default   => send_error('Endpoint not found', 404)
};
include 'API/Alerting.php';
require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();