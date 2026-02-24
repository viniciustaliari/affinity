<?php

function getContextosCanonicos(): array
{
    return [
        1 => "publicidad",
        2 => "todos los servicios",
        3 => "tension arterial",
        4 => "menu seleccion",
        5 => "pago",
        6 => "reposo",
        7 => "clima",
        8 => "peso altura"
    ];
}

function getContextosServicioCanonicos(): array
{
    return [
        2 => "todos los servicios",
        3 => "tension arterial",
        8 => "peso/altura",
        9 => "OXI",
        10 => "IMG"
    ];
}

function isContextoServicio(int $contextoId): bool
{
    return getServicioIdPorContextoId($contextoId) !== null;
}

function getJsonCategoryByContextoId(int $contextoId): string
{
    $mapById = [
        1 => "ads",
        2 => "allservices",
        3 => "bloodpressure",
        4 => "menuselection",
        5 => "payment",
        6 => "standby",
        7 => "weather",
        8 => "weightheight"
    ];

    return $mapById[$contextoId] ?? "unknown";
}

function getJsonCategoryByContextoNombre(string $contextoNombre): string
{
    $key = strtolower(trim($contextoNombre));
    if (function_exists('iconv')) {
        $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $key);
        if ($ascii !== false) {
            $key = strtolower($ascii);
        }
    }
    $key = str_replace(["_", "-"], [" ", " "], $key);
    $key = preg_replace('/\s+/', ' ', $key) ?? $key;

    $mapByName = [
        "publicidad" => "ads",
        "todos los servicios" => "allservices",
        "tension arterial" => "bloodpressure",
        "tensiÃ³n arterial" => "bloodpressure",
        "menu seleccion" => "menuselection",
        "pago" => "payment",
        "reposo" => "standby",
        "clima" => "weather",
        "peso altura" => "weightheight",
        "peso/altura" => "weightheight",
        "ads" => "ads",
        "allservices" => "allservices",
        "bloodpressure" => "bloodpressure",
        "menuselection" => "menuselection",
        "payment" => "payment",
        "standby" => "standby",
        "weather" => "weather",
        "weightheight" => "weightheight",
        "unknown" => "unknown"
    ];

    return $mapByName[$key] ?? "unknown";
}

function getJsonCategoryFromContexto(string $contextoRaw, $database = null): string
{
    $contextoRaw = trim($contextoRaw);
    if ($contextoRaw === "") {
        return "unknown";
    }

    if (ctype_digit($contextoRaw)) {
        $contextoId = intval($contextoRaw);
        $byId = getJsonCategoryByContextoId($contextoId);
        if ($byId !== "unknown") {
            return $byId;
        }

        if ($database) {
            $nombre = getContextoNombrePorId($database, $contextoId);
            if (is_string($nombre) && trim($nombre) !== "") {
                return getJsonCategoryByContextoNombre($nombre);
            }
        }

        return "unknown";
    }

    return getJsonCategoryByContextoNombre($contextoRaw);
}

function getContextoNombreCanonicoPorId(int $contextoId): ?string
{
    $contextos = getContextosCanonicos();
    return $contextos[$contextoId] ?? null;
}

function getContextoNombrePorId($database, int $contextoId): ?string
{
    if ($contextoId <= 0) {
        return null;
    }

    try {
        $nombre = $database->get("contextos", "nombre", ["id" => $contextoId]);
        if (is_string($nombre) && trim($nombre) !== "") {
            return trim($nombre);
        }
    } catch (\Throwable $e) {
        // Si falla la consulta, usamos fallback canonico.
    }

    return getContextoNombreCanonicoPorId($contextoId);
}

function ensureContextosCanonicos($database): void
{
    $contextos = getContextosCanonicos();

    foreach ($contextos as $id => $nombre) {
        try {
            $existe = $database->has("contextos", ["id" => $id]);
            if ($existe) {
                $database->update("contextos", ["nombre" => $nombre], ["id" => $id]);
            } else {
                $database->insert("contextos", ["id" => $id, "nombre" => $nombre]);
            }
        } catch (\Throwable $e) {
            // Si falla la sincronizacion, no bloqueamos la app.
        }
    }
}

function getServicioIdPorContextoId(int $contextoId): ?int
{
    $mapById = [
        2 => 5, // todos los servicios
        3 => 2, // tension arterial
        8 => 1, // peso altura
        9 => 3, // OXI
        10 => 4 // IMG
    ];

    return $mapById[$contextoId] ?? null;
}

