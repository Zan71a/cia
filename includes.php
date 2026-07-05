<?php
declare(strict_types=1);

function data_path(): string
{
    return __DIR__ . '/data/content.json';
}

function load_content(): array
{
    $path = data_path();
    if (!file_exists($path)) {
        return [];
    }

    $json = file_get_contents($path);
    $data = json_decode($json ?: '{}', true);
    return is_array($data) ? $data : [];
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function asset_url(?string $path): string
{
    $path = trim((string) $path);
    return $path !== '' ? $path : 'https://placehold.co/800x600/000/fff?text=BHINEKA.SPACE';
}

function nl_to_br(?string $text): string
{
    return nl2br(e((string) $text));
}
