<?php

function json_response(mixed $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function cors_json_headers(string $methods): void
{
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json; charset=UTF-8');
    header("Access-Control-Allow-Methods: {$methods}");
    header('Access-Control-Max-Age: 3600');
    header('Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With, X-API-KEY');
}

function redirect_to(string $path): void
{
    header('Location: ' . ($path ?: '/'));
    exit;
}

function redirect_with_id(string $path, int|string $id): void
{
    $path = $path ?: '/';
    $separator = str_contains($path, '?') ? '&' : '?';
    redirect_to($path . $separator . 'id=' . $id);
}

function fail(int $status, string $message): void
{
    json_response(['error' => $message], $status);
}
