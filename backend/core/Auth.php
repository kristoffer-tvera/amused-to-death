<?php

function current_user(): ?string
{
    return $_SESSION['auth'] ?? null;
}

function is_admin(): bool
{
    return !empty($_SESSION['admin']);
}

function require_auth(): void
{
    if (empty($_SESSION['auth'])) {
        fail(401, 'Unauthorized');
    }
}

function require_admin(): void
{
    if (empty($_SESSION['admin'])) {
        fail(401, 'Unauthorized');
    }
}

function require_api_key(): void
{
    $provided = $_SERVER['HTTP_X_API_KEY'] ?? '';
    if (($GLOBALS['x_api_key'] ?? '') !== $provided) {
        fail(401, 'Bad API key');
    }
}
