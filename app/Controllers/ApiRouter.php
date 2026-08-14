<?php

declare(strict_types=1);

header('Content-Type: application/json');

$segment = substr($uri, strlen('/api'));
$segment = rtrim($segment, '/') ?: '/';

match (true) {
    $segment === '/health'
        => (function () { echo json_encode(['status' => 'ok']); })(),

    default
        => (function () {
            http_response_code(404);
            echo json_encode(['error' => 'Endpunkt nicht gefunden']);
        })(),
};
