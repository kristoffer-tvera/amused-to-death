<?php

function input_from(array $source, string $key, mixed $default = ''): mixed
{
    if (!isset($source[$key])) {
        return $default;
    }

    if (is_array($source[$key])) {
        return $default;
    }

    return htmlspecialchars(strip_tags((string)$source[$key]));
}

function post_value(string $key, mixed $default = ''): mixed
{
    return input_from($_POST, $key, $default);
}

function query_value(string $key, mixed $default = ''): mixed
{
    return input_from($_GET, $key, $default);
}

function int_value(mixed $value, int $default = 0): int
{
    if ($value === null || $value === '') {
        return $default;
    }

    return intval($value);
}

function boolish(mixed $value): int
{
    return empty($value) ? 0 : intval($value);
}

function nullable_main(mixed $value): ?int
{
    $main = int_value($value, -1);
    return $main === -1 ? null : $main;
}
