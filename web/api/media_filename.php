<?php

function sanitizeClientMediaFileName(string $rawName, string $fallback = 'media.bin'): string
{
    $rawName = str_replace('\\', '/', $rawName);
    $name = basename(trim($rawName));

    if ($name === '') {
        return $fallback;
    }

    $name = preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?? $name;
    $name = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '_', $name);
    $name = trim($name);

    if ($name === '' || $name === '.' || $name === '..') {
        return $fallback;
    }

    return $name;
}

function detectUploadMimeType(array $file): string
{
    $tmp = (string)($file['tmp_name'] ?? '');
    if ($tmp !== '' && is_file($tmp) && function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mime = finfo_file($finfo, $tmp);
            finfo_close($finfo);
            if (is_string($mime) && $mime !== '') {
                return strtolower(trim($mime));
            }
        }
    }

    $fallback = strtolower(trim((string)($file['type'] ?? '')));
    return $fallback;
}

function isImageMime(string $mime): bool
{
    return str_starts_with(strtolower(trim($mime)), 'image/');
}

function isVideoMime(string $mime): bool
{
    return str_starts_with(strtolower(trim($mime)), 'video/');
}

function normalizeMediaExtension(string $mime, string $originalName, string $kind = ''): string
{
    $mime = strtolower(trim($mime));

    $map = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'image/bmp' => 'bmp',
        'image/svg+xml' => 'svg',
        'video/mp4' => 'mp4',
        'video/webm' => 'webm',
        'video/ogg' => 'ogv',
        'video/quicktime' => 'mov',
        'video/x-msvideo' => 'avi',
        'video/x-matroska' => 'mkv',
    ];

    if (isset($map[$mime])) {
        return $map[$mime];
    }

    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if ($ext !== '' && preg_match('/^[a-z0-9]{2,6}$/', $ext)) {
        return $ext;
    }

    if ($kind === 'image') {
        return 'jpg';
    }
    if ($kind === 'video') {
        return 'mp4';
    }
    return 'bin';
}
