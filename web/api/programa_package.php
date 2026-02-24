<?php
require_once __DIR__ . '/programa_payload.php';

function buildProgramaZipByProgramId(int $programId): array
{
    if ($programId <= 0) {
        return [
            "status" => "error",
            "error" => "Missing or invalid program_id."
        ];
    }

    if (!class_exists('ZipArchive')) {
        return [
            "status" => "error",
            "error" => "ZipArchive extension is not available in PHP."
        ];
    }

    $payload = buildProgramaPayloadByProgramId($programId);
    if (isset($payload["error"])) {
        return [
            "status" => "error",
            "error" => (string)$payload["error"],
            "program_id" => $programId
        ];
    }

    $manifestWithInternal = buildProgramaManifestForZip($payload);
    $manifest = stripInternalManifestFields($manifestWithInternal);
    $tmpDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'affinity_pkg_' . uniqid('', true);
    $mediaDir = $tmpDir . DIRECTORY_SEPARATOR . 'media';
    $zipPath = $tmpDir . '.zip';

    if (!@mkdir($mediaDir, 0777, true) && !is_dir($mediaDir)) {
        return [
            "status" => "error",
            "error" => "Could not create temporary package directory."
        ];
    }

    $missingFiles = copyProgramMediaForPackage($manifestWithInternal, $mediaDir);

    $manifestJson = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    if ($manifestJson === false || @file_put_contents($tmpDir . DIRECTORY_SEPARATOR . 'program.json', $manifestJson) === false) {
        removeDirectoryRecursive($tmpDir);
        @unlink($zipPath);
        return [
            "status" => "error",
            "error" => "Could not write program manifest JSON."
        ];
    }

    $zip = new ZipArchive();
    $openResult = $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    if ($openResult !== true) {
        removeDirectoryRecursive($tmpDir);
        @unlink($zipPath);
        return [
            "status" => "error",
            "error" => "Could not create zip package."
        ];
    }

    addDirectoryToZip($zip, $tmpDir);
    $zip->close();

    $zipBinary = @file_get_contents($zipPath);
    if ($zipBinary === false) {
        removeDirectoryRecursive($tmpDir);
        @unlink($zipPath);
        return [
            "status" => "error",
            "error" => "Could not read generated zip package."
        ];
    }

    $zipBase64 = base64_encode($zipBinary);
    $zipName = buildZipName($payload, $programId);
    $zipSize = strlen($zipBinary);

    removeDirectoryRecursive($tmpDir);
    @unlink($zipPath);

    return [
        "status" => "ok",
        "program_id" => $programId,
        "program_name" => (string)($payload["programName"] ?? ("program_" . $programId)),
        "contexto" => (string)($payload["contexto"] ?? ""),
        "zip_name" => $zipName,
        "zip_size_bytes" => $zipSize,
        "zip_base64" => $zipBase64,
        "manifest" => $manifest,
        "missing_files" => $missingFiles
    ];
}

function saveProgramaZipSnapshotToDatabase(int $programId): array
{
    if ($programId <= 0) {
        return [
            "status" => "error",
            "error" => "Missing or invalid program_id."
        ];
    }

    $database = $GLOBALS['database'] ?? null;
    if (!$database) {
        return [
            "status" => "error",
            "error" => "Database connection is not available."
        ];
    }

    $package = buildProgramaZipByProgramId($programId);
    if (($package["status"] ?? "") !== "ok") {
        return $package;
    }

    $zipBase64 = (string)($package["zip_base64"] ?? "");
    $zipBinary = base64_decode($zipBase64, true);
    if ($zipBinary === false) {
        return [
            "status" => "error",
            "error" => "Could not decode generated zip package."
        ];
    }

    $manifest = $package["manifest"] ?? [];
    $missingFiles = $package["missing_files"] ?? [];

    $manifestJson = json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($manifestJson === false) {
        $manifestJson = '{}';
    }

    $missingFilesJson = json_encode($missingFiles, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($missingFilesJson === false) {
        $missingFilesJson = '[]';
    }

    $maxVersion = $database->max("programa_paquetes", "version", [
        "id_programa" => $programId
    ]);
    $version = ((int)($maxVersion ?? 0)) + 1;

    $category = (string)($manifest["category"] ?? "unknown");
    $zipSizeBytes = (int)($package["zip_size_bytes"] ?? strlen($zipBinary));

    try {
        $database->insert("programa_paquetes", [
            "id_programa" => $programId,
            "version" => $version,
            "program_name" => (string)($package["program_name"] ?? ("program_" . $programId)),
            "contexto" => (string)($package["contexto"] ?? ""),
            "category" => $category,
            "zip_name" => (string)($package["zip_name"] ?? ("program_" . $programId . ".zip")),
            "zip_size_bytes" => $zipSizeBytes,
            "zip_sha256" => hash('sha256', $zipBinary),
            "zip_blob" => $zipBinary,
            "manifest_json" => $manifestJson,
            "missing_files_json" => $missingFilesJson
        ]);
    } catch (\Throwable $e) {
        return [
            "status" => "error",
            "error" => "Could not persist zip package in database."
        ];
    }

    $packageId = (int)$database->id();
    if ($packageId <= 0) {
        return [
            "status" => "error",
            "error" => "Could not persist zip package in database."
        ];
    }

    $package["package_id"] = $packageId;
    $package["version"] = $version;
    $package["category"] = $category;

    return $package;
}

function getLatestProgramaZipSnapshotFromDatabase(int $programId): array
{
    if ($programId <= 0) {
        return [
            "status" => "error",
            "error" => "Missing or invalid program_id."
        ];
    }

    $database = $GLOBALS['database'] ?? null;
    if (!$database) {
        return [
            "status" => "error",
            "error" => "Database connection is not available."
        ];
    }

    $row = $database->get("programa_paquetes", "*", [
        "id_programa" => $programId,
        "ORDER" => [
            "version" => "DESC",
            "id" => "DESC"
        ]
    ]);

    if (!$row) {
        return [
            "status" => "error",
            "error" => "No package found for program_id.",
            "code" => "no_package_found"
        ];
    }

    $zipBinary = $row["zip_blob"] ?? null;
    if (!is_string($zipBinary) || $zipBinary === "") {
        return [
            "status" => "error",
            "error" => "Stored zip package is empty or unreadable.",
            "code" => "invalid_package_blob"
        ];
    }

    $manifest = json_decode((string)($row["manifest_json"] ?? "{}"), true);
    if (!is_array($manifest)) {
        $manifest = [];
    }

    $missingFiles = json_decode((string)($row["missing_files_json"] ?? "[]"), true);
    if (!is_array($missingFiles)) {
        $missingFiles = [];
    }

    return [
        "status" => "ok",
        "program_id" => (int)$row["id_programa"],
        "program_name" => (string)$row["program_name"],
        "contexto" => (string)$row["contexto"],
        "category" => (string)$row["category"],
        "zip_name" => (string)$row["zip_name"],
        "zip_size_bytes" => (int)$row["zip_size_bytes"],
        "zip_base64" => base64_encode($zipBinary),
        "manifest" => $manifest,
        "missing_files" => $missingFiles,
        "package_id" => (int)$row["id"],
        "version" => (int)$row["version"]
    ];
}

function getOrBuildProgramaZipSnapshotFromDatabase(int $programId): array
{
    $latest = getLatestProgramaZipSnapshotFromDatabase($programId);
    if (($latest["status"] ?? "") === "ok") {
        $database = $GLOBALS['database'] ?? null;
        if ($database) {
            $programa = $database->get("programas", ["nombre", "contexto"], ["id" => $programId]);
            if ($programa) {
                $currentName = trim((string)($programa["nombre"] ?? ""));
                $currentContexto = trim((string)($programa["contexto"] ?? ""));
                $currentCategory = getJsonCategoryFromContexto($currentContexto, $database);

                $latestName = trim((string)($latest["program_name"] ?? ""));
                $latestContexto = trim((string)($latest["contexto"] ?? ""));
                $latestCategory = trim((string)($latest["category"] ?? ""));

                $snapshotOutdated =
                    $currentContexto !== $latestContexto ||
                    $currentCategory !== $latestCategory ||
                    ($currentName !== '' && $currentName !== $latestName);

                if ($snapshotOutdated) {
                    $rebuilt = saveProgramaZipSnapshotToDatabase($programId);
                    if (($rebuilt["status"] ?? "") === "ok") {
                        return $rebuilt;
                    }
                }
            }
        }

        return $latest;
    }

    if (($latest["code"] ?? "") !== "no_package_found") {
        return $latest;
    }

    return saveProgramaZipSnapshotToDatabase($programId);
}

function getProgramMediaOriginalNameMaps(int $programId): array
{
    $maps = [
        "images" => [],
        "videos" => []
    ];

    if ($programId <= 0) {
        return $maps;
    }

    $database = $GLOBALS['database'] ?? null;
    if (!$database) {
        return $maps;
    }

    try {
        $rows = $database->select("imagenes", ["nombre", "nombre_original"], [
            "id_programa" => $programId
        ]);
    } catch (\Throwable $e) {
        $rows = $database->select("imagenes", ["nombre"], [
            "id_programa" => $programId
        ]);
    }

    foreach ($rows as $row) {
        $stored = normalizeMediaName((string)($row["nombre"] ?? ""));
        if ($stored === "") {
            continue;
        }
        $original = normalizeMediaName((string)($row["nombre_original"] ?? ""));
        $maps["images"][$stored] = $original !== "" ? $original : $stored;
    }

    try {
        $rows = $database->select("video", ["nombre", "nombre_original"], [
            "id_programa" => $programId
        ]);
    } catch (\Throwable $e) {
        $rows = $database->select("video", ["nombre"], [
            "id_programa" => $programId
        ]);
    }

    foreach ($rows as $row) {
        $stored = normalizeMediaName((string)($row["nombre"] ?? ""));
        if ($stored === "") {
            continue;
        }
        $original = normalizeMediaName((string)($row["nombre_original"] ?? ""));
        $maps["videos"][$stored] = $original !== "" ? $original : $stored;
    }

    return $maps;
}

function buildProgramaManifestForZip(array $payload): array
{
    $defaultImageDuration = 3;
    $fallbackCategory = "unknown";
    $contextoRaw = isset($payload["contexto"]) ? (string)$payload["contexto"] : "";
    $database = $GLOBALS['database'] ?? null;
    $fallbackCategory = getJsonCategoryFromContexto($contextoRaw, $database);
    $programId = (int)($payload["programId"] ?? 0);
    $nameMaps = getProgramMediaOriginalNameMaps($programId);
    $usedMediaNames = [];

    $images = [];
    foreach (($payload["images"] ?? []) as $img) {
        $sourceName = normalizeMediaName((string)($img["name"] ?? ""));
        if ($sourceName === "") {
            continue;
        }
        $preferredName = (string)($nameMaps["images"][$sourceName] ?? $sourceName);
        $outputName = makeUniqueMediaName(normalizeMediaName($preferredName), $usedMediaNames, $sourceName);

        $images[] = [
            "duration" => max(1, (int)($img["duration"] ?? $defaultImageDuration)),
            "fit" => (string)($img["fit"] ?? "cover"),
            "name" => $outputName,
            "description" => (string)($img["description"] ?? "Archivo multimedia desde configurador"),
            "index" => (int)($img["index"] ?? 0),
            "mediaType" => "image",
            "format" => (string)($img["format"] ?? strtolower(pathinfo($outputName, PATHINFO_EXTENSION))),
            "transition" => (string)($img["transition"] ?? "fade"),
            "__sourceName" => $sourceName
        ];
    }

    $videos = [];
    foreach (($payload["videos"] ?? []) as $vid) {
        $sourceName = normalizeMediaName((string)($vid["name"] ?? ""));
        if ($sourceName === "") {
            continue;
        }
        $preferredName = (string)($nameMaps["videos"][$sourceName] ?? $sourceName);
        $outputName = makeUniqueMediaName(normalizeMediaName($preferredName), $usedMediaNames, $sourceName);

        $videos[] = [
            "name" => $outputName,
            "description" => (string)($vid["description"] ?? "Archivo multimedia desde configurador"),
            "index" => (int)($vid["index"] ?? 0),
            "mute" => (bool)($vid["mute"] ?? false),
            "format" => (string)($vid["format"] ?? strtolower(pathinfo($outputName, PATHINFO_EXTENSION))),
            "mediaType" => "video",
            "__sourceName" => $sourceName
        ];
    }

    usort($images, static function ($a, $b) {
        return ((int)$a["index"]) <=> ((int)$b["index"]);
    });
    usort($videos, static function ($a, $b) {
        return ((int)$a["index"]) <=> ((int)$b["index"]);
    });

    return [
        "defaults" => [
            "imageDuration" => $defaultImageDuration
        ],
        "programName" => (string)($payload["programName"] ?? $fallbackCategory),
        "images" => $images,
        "videos" => $videos,
        "packageName" => (string)($payload["packageName"] ?? "media"),
        "category" => $fallbackCategory,
        "timestamp" => (int)($payload["timestamp"] ?? round(microtime(true) * 1000))
    ];
}

function copyProgramMediaForPackage(array $manifest, string $mediaDir): array
{
    $missing = [];
    $imagesBase = realpath(__DIR__ . '/../data/images');
    $videosBase = realpath(__DIR__ . '/../data/videos');

    foreach (($manifest["images"] ?? []) as $img) {
        $name = (string)($img["name"] ?? "");
        $sourceName = (string)($img["__sourceName"] ?? $name);
        if ($name === "" || !$imagesBase) {
            $missing[] = ["type" => "image", "name" => $name, "source" => $sourceName, "reason" => "missing_source_base"];
            continue;
        }
        $src = $imagesBase . DIRECTORY_SEPARATOR . $sourceName;
        $dst = $mediaDir . DIRECTORY_SEPARATOR . $name;
        if (!is_file($src)) {
            $missing[] = ["type" => "image", "name" => $name, "source" => $sourceName, "reason" => "file_not_found"];
            continue;
        }
        @mkdir(dirname($dst), 0777, true);
        if (!@copy($src, $dst)) {
            $missing[] = ["type" => "image", "name" => $name, "source" => $sourceName, "reason" => "copy_failed"];
        }
    }

    foreach (($manifest["videos"] ?? []) as $vid) {
        $name = (string)($vid["name"] ?? "");
        $sourceName = (string)($vid["__sourceName"] ?? $name);
        if ($name === "" || !$videosBase) {
            $missing[] = ["type" => "video", "name" => $name, "source" => $sourceName, "reason" => "missing_source_base"];
            continue;
        }
        $src = $videosBase . DIRECTORY_SEPARATOR . $sourceName;
        $dst = $mediaDir . DIRECTORY_SEPARATOR . $name;
        if (!is_file($src)) {
            $missing[] = ["type" => "video", "name" => $name, "source" => $sourceName, "reason" => "file_not_found"];
            continue;
        }
        @mkdir(dirname($dst), 0777, true);
        if (!@copy($src, $dst)) {
            $missing[] = ["type" => "video", "name" => $name, "source" => $sourceName, "reason" => "copy_failed"];
        }
    }

    return $missing;
}

function stripInternalManifestFields(array $manifest): array
{
    foreach (["images", "videos"] as $key) {
        if (!isset($manifest[$key]) || !is_array($manifest[$key])) {
            continue;
        }

        foreach ($manifest[$key] as $idx => $item) {
            if (!is_array($item)) {
                continue;
            }
            unset($item["__sourceName"]);
            $manifest[$key][$idx] = $item;
        }
    }

    return $manifest;
}

function makeUniqueMediaName(string $preferredName, array &$usedNames, string $fallbackName = 'media.bin'): string
{
    $name = $preferredName !== '' ? $preferredName : $fallbackName;
    $name = normalizeMediaName($name);
    if ($name === '') {
        $name = normalizeMediaName($fallbackName);
    }
    if ($name === '') {
        $name = 'media.bin';
    }

    $key = strtolower($name);
    if (!isset($usedNames[$key])) {
        $usedNames[$key] = true;
        return $name;
    }

    $ext = pathinfo($name, PATHINFO_EXTENSION);
    $base = pathinfo($name, PATHINFO_FILENAME);
    if ($base === '') {
        $base = 'media';
    }

    $n = 2;
    while (true) {
        $candidate = $base . '_' . $n . ($ext !== '' ? ('.' . $ext) : '');
        $candidate = normalizeMediaName($candidate);
        if ($candidate === '') {
            $n++;
            continue;
        }
        $candidateKey = strtolower($candidate);
        if (!isset($usedNames[$candidateKey])) {
            $usedNames[$candidateKey] = true;
            return $candidate;
        }
        $n++;
    }
}

function addDirectoryToZip(ZipArchive $zip, string $sourceDir): void
{
    $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $entry) {
        $path = $entry->getPathname();
        $relative = substr($path, strlen($sourceDir) + 1);
        $relative = str_replace('\\', '/', $relative);

        if ($entry->isDir()) {
            $zip->addEmptyDir($relative);
        } else {
            $zip->addFile($path, $relative);
        }
    }
}

function removeDirectoryRecursive(string $dir): void
{
    if (!is_dir($dir)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            @rmdir($item->getPathname());
        } else {
            @unlink($item->getPathname());
        }
    }

    @rmdir($dir);
}

function normalizeMediaName(string $name): string
{
    $name = trim($name);
    if ($name === "") {
        return "";
    }
    return basename(str_replace('\\', '/', $name));
}

function buildZipName(array $payload, int $programId): string
{
    $name = (string)($payload["programName"] ?? ("program_" . $programId));
    $name = trim($name);
    $name = preg_replace('/[^a-zA-Z0-9_-]+/', '_', $name);
    if ($name === null || $name === '') {
        $name = "program_" . $programId;
    }

    return $name . '.zip';
}
