<?php
require __DIR__ . '/db.php';
header('Content-Type: application/json');

/* ===============================
   Defaults globales del sistema
   =============================== */

$defaults = [
    "imageDurationMs" => 5000,
    "videoRepeat"     => 1
];

/* ===============================
   Obtener 1 programa por CONTEXTO
   =============================== */

$contexto = isset($_GET["contexto"]) ? trim($_GET["contexto"]) : "";

if ($contexto === "") {
    http_response_code(400);
    echo json_encode([
        "error" => "Missing or invalid 'contexto' query param. Example: ?contexto=1"
    ], JSON_PRETTY_PRINT);
    exit;
}

/**
 * Asumimos que en la tabla 'programas' existe una columna llamada 'contexto'.
 * Si el nombre real difiere, cámbialo aquí.
 */
$programa = $database->get("programas", "*", [
    "contexto" => $contexto
]);

if (!$programa) {
    http_response_code(404);
    echo json_encode([
        "error"    => "Program not found for contexto",
        "contexto" => $contexto
    ], JSON_PRETTY_PRINT);
    exit;
}

$id = (int)$programa["id"];

$imagenes = $database->select("imagenes", "*", [
    "id_programa" => $id
]);

$videos = $database->select("video", "*", [
    "id_programa" => $id
]);

/* ===============================
   Construcción de respuesta
   (SIN programs[])
   =============================== */

$resultado = [
    "timestamp"   => round(microtime(true) * 1000),
    "defaults"    => $defaults,
    "programName" => $programa["nombre"],
    "category"    => getJsonCategoryFromContexto($contexto, $database),
    "packageName" => $programa["packageName"] ?? "media",
    "images"      => [],
    "videos"      => []
];

/* -------- IMÁGENES --------
   - format en vez de extension
   - duration en segundos
*/

foreach ($imagenes as $img) {

    $format = strtolower(pathinfo($img["nombre"], PATHINFO_EXTENSION));

    // En tu código original duracion se multiplicaba por 1000,
    // así que asumimos que BD guarda segundos.
    $durationSeconds = isset($img["duracion"])
        ? (int)$img["duracion"]
        : (int)round($defaults["imageDurationMs"] / 1000);

    // Si viene 0 o negativo, usamos el default
    if ($durationSeconds <= 0) {
        $durationSeconds = (int)round($defaults["imageDurationMs"] / 1000);
    }

    $resultado["images"][] = [
        "index"       => (int)($img["indice"] ?? 0),
        "name"        => $img["nombre"],
        "mediaType"   => "image",
        "description" => "Archivo multimedia desde configurador",
        "format"      => $format,
        "duration"    => $durationSeconds, // ✅ segundos
        "fit"         => $img["fit"] ?? "cover",
        "transition"  => $img["transition"] ?? "fade"
    ];
}

/* -------- VIDEOS --------
   - format en vez de extension
*/

foreach ($videos as $vid) {

    $format = strtolower(pathinfo($vid["nombre"], PATHINFO_EXTENSION));

    $resultado["videos"][] = [
        "index"       => (int)($vid["indice"] ?? 0),
        "name"        => $vid["nombre"],
        "description" => "Archivo multimedia desde configurador",
        "mediaType"   => "video",
        "format"      => $format,
        "repeat"      => isset($vid["repeat"])
            ? (int)$vid["repeat"]
            : (int)$defaults["videoRepeat"],
        "mute"        => (bool)($vid["mute"] ?? 0),
        "startMs"     => (int)($vid["start_ms"] ?? 0),
        "endMs"       => $vid["end_ms"] ?? null
    ];
}

/* ===============================
   Respuesta final
   =============================== */

echo json_encode($resultado, JSON_PRETTY_PRINT);
