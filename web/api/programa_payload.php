<?php
require __DIR__ . '/db.php';

function resolveCategoriaFromContexto(string $contexto): string
{
    $database = $GLOBALS['database'] ?? null;
    return getJsonCategoryFromContexto($contexto, $database);
}

function buildProgramaPayload(string $contexto): array
{
    $contexto = trim($contexto);
    if ($contexto === "") {
        return [
            "error" => "Missing or invalid 'contexto'. Example: ?contexto=1"
        ];
    }

    // db.php debe dejar $database disponible (Medoo u otro)
    $database = $GLOBALS['database'];

    $programa = $database->get("programas", "*", [
        "contexto" => $contexto
    ]);

    if (!$programa) {
        return [
            "error"    => "Program not found for contexto",
            "contexto" => $contexto
        ];
    }

    return buildProgramaPayloadFromPrograma($programa, $contexto);
}

function buildProgramaPayloadByProgramId(int $programId): array
{
    if ($programId <= 0) {
        return [
            "error" => "Missing or invalid 'program_id'."
        ];
    }

    $database = $GLOBALS['database'];
    $programa = $database->get("programas", "*", [
        "id" => $programId
    ]);

    if (!$programa) {
        return [
            "error" => "Program not found for program_id",
            "program_id" => $programId
        ];
    }

    $contexto = isset($programa["contexto"]) ? (string)$programa["contexto"] : "";
    return buildProgramaPayloadFromPrograma($programa, $contexto);
}

function buildProgramaPayloadFromPrograma(array $programa, string $contexto): array
{
    $defaults = [
        "imageDurationMs" => 5000,
        "videoRepeat"     => 1
    ];

    $database = $GLOBALS['database'];
    $id = (int)$programa["id"];

    $imagenes = $database->select("imagenes", "*", [
        "id_programa" => $id
    ]);

    $videos = $database->select("video", "*", [
        "id_programa" => $id
    ]);

    $resultado = [
        "timestamp"   => (int) round(microtime(true) * 1000),
        "defaults"    => $defaults,
        "programId"   => $id,
        "programName" => $programa["nombre"],
        "contexto"    => $contexto,
        "category"    => resolveCategoriaFromContexto($contexto),
        "packageName" => $programa["packageName"] ?? "media",
        "images"      => [],
        "videos"      => []
    ];

    foreach ($imagenes as $img) {
        $format = strtolower(pathinfo($img["nombre"], PATHINFO_EXTENSION));

        $durationSeconds = isset($img["duracion"])
            ? (int)$img["duracion"]
            : (int)round($defaults["imageDurationMs"] / 1000);

        if ($durationSeconds <= 0) {
            $durationSeconds = (int)round($defaults["imageDurationMs"] / 1000);
        }

        $resultado["images"][] = [
            "index"       => (int)($img["indice"] ?? 0),
            "name"        => $img["nombre"],
            "mediaType"   => "image",
            "description" => "Archivo multimedia desde configurador",
            "format"      => $format,
            "duration"    => $durationSeconds,
            "fit"         => $img["fit"] ?? "cover",
            "transition"  => $img["transition"] ?? "fade"
        ];
    }

    foreach ($videos as $vid) {
        $format = strtolower(pathinfo($vid["nombre"], PATHINFO_EXTENSION));

        $resultado["videos"][] = [
            "index"       => (int)($vid["indice"] ?? 0),
            "name"        => $vid["nombre"],
            "description" => "Archivo multimedia desde configurador",
            "mediaType"   => "video",
            "format"      => $format,
            "repeat"      => isset($vid["repeat"]) ? (int)$vid["repeat"] : (int)$defaults["videoRepeat"],
            "mute"        => (bool)($vid["mute"] ?? 0),
            "startMs"     => (int)($vid["start_ms"] ?? 0),
            "endMs"       => $vid["end_ms"] ?? null
        ];
    }

    return $resultado;
}
