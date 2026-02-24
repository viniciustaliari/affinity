<?php
header('X-Content-Type-Options: nosniff');

$bucketRaw = strtolower(trim((string)($_GET['bucket'] ?? '')));
$nameRaw = (string)($_GET['name'] ?? '');
$name = basename($nameRaw);

$allowedBuckets = [
    'images' => __DIR__ . '/../data/images/',
    'videos' => __DIR__ . '/../data/videos/',
];

if (!isset($allowedBuckets[$bucketRaw]) || $name === '') {
    http_response_code(400);
    echo 'Bad request';
    exit;
}

$base = $allowedBuckets[$bucketRaw];
$path = $base . $name;

if (!is_file($path)) {
    http_response_code(404);
    echo 'Not found';
    exit;
}

$mime = '';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo !== false) {
        $detected = finfo_file($finfo, $path);
        finfo_close($finfo);
        if (is_string($detected) && $detected !== '') {
            $mime = $detected;
        }
    }
}

if ($mime === '') {
    $mime = 'application/octet-stream';
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . (string)filesize($path));
header('Cache-Control: public, max-age=300');
readfile($path);
