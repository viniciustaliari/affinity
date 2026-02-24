<?php
require __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/contextos.php';

use Medoo\Medoo;

$database = new Medoo([
    'type' => 'mysql',
    'host' => 'db',
    'database' => 'affinity',   // cambia al nombre real de tu BD
    'username' => 'affinity',
    'password' => 'affinity',
    'charset' => 'utf8mb4'
]);

// Expone la conexion en el scope global para funciones cargadas
// desde contexto CLI/WebSocket (incluidas dentro de metodos).
$GLOBALS['database'] = $database;

if (!function_exists('ensureProgramaPaquetesSchema')) {
    function ensureProgramaPaquetesSchema(Medoo $database): void
    {
        try {
            $database->query(
                "CREATE TABLE IF NOT EXISTS `programa_paquetes` (
                    `id` bigint NOT NULL AUTO_INCREMENT,
                    `id_programa` int NOT NULL,
                    `version` int NOT NULL,
                    `program_name` varchar(255) NOT NULL,
                    `contexto` varchar(32) NOT NULL,
                    `category` varchar(64) NOT NULL,
                    `zip_name` varchar(255) NOT NULL,
                    `zip_size_bytes` int unsigned NOT NULL,
                    `zip_sha256` char(64) NOT NULL,
                    `zip_blob` longblob NOT NULL,
                    `manifest_json` longtext DEFAULT NULL,
                    `missing_files_json` longtext DEFAULT NULL,
                    `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`id`),
                    UNIQUE KEY `uq_programa_version` (`id_programa`,`version`),
                    KEY `idx_programa_created` (`id_programa`,`created_at`),
                    CONSTRAINT `fk_paquete_programa`
                        FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`)
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
            );
        } catch (\Throwable $e) {
            // No bloqueamos la app si la auto-migracion falla.
        }
    }
}

if (!function_exists('ensureProgramasEnviadosActivosSchema')) {
    function ensureProgramasEnviadosActivosSchema(Medoo $database): void
    {
        try {
            $database->query(
                "CREATE TABLE IF NOT EXISTS `programas_enviados_activos` (
                    `contexto` varchar(32) NOT NULL,
                    `id_programa` int NOT NULL,
                    `program_name` varchar(255) NOT NULL,
                    `sent_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (`contexto`),
                    KEY `idx_programa_sent_at` (`sent_at`),
                    KEY `idx_programa_id` (`id_programa`),
                    CONSTRAINT `fk_programa_enviado_programa`
                        FOREIGN KEY (`id_programa`) REFERENCES `programas` (`id`)
                        ON DELETE CASCADE ON UPDATE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;"
            );
        } catch (\Throwable $e) {
            // No bloqueamos la app si la auto-migracion falla.
        }
    }
}

if (!function_exists('ensureMediaOriginalNameColumns')) {
    function ensureMediaOriginalNameColumns(Medoo $database): void
    {
        try {
            if (!tableHasColumn($database, 'imagenes', 'nombre_original')) {
                $database->query("ALTER TABLE `imagenes` ADD COLUMN `nombre_original` varchar(255) DEFAULT NULL AFTER `nombre`;");
            }
        } catch (\Throwable $e) {
            // No bloqueamos la app si la auto-migracion falla.
        }

        try {
            if (!tableHasColumn($database, 'video', 'nombre_original')) {
                $database->query("ALTER TABLE `video` ADD COLUMN `nombre_original` varchar(255) DEFAULT NULL AFTER `nombre`;");
            }
        } catch (\Throwable $e) {
            // No bloqueamos la app si la auto-migracion falla.
        }
    }
}

if (!function_exists('tableHasColumn')) {
    function tableHasColumn(Medoo $database, string $table, string $column): bool
    {
        $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table) ?? '';
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column) ?? '';
        if ($table === '' || $column === '') {
            return false;
        }

        $stmt = $database->query("SHOW COLUMNS FROM `{$table}` LIKE '{$column}';");
        if (!$stmt) {
            return false;
        }
        return $stmt->fetch() !== false;
    }
}

ensureContextosCanonicos($database);
ensureProgramaPaquetesSchema($database);
ensureProgramasEnviadosActivosSchema($database);
ensureMediaOriginalNameColumns($database);

// $database = new Medoo([
//     'type' => 'mysql',
//     'database' => 'dbs15060883',
//     'host' => 'db5019180537.hosting-data.io',
//     'username' => 'dbu3083527',
//     'password' => '4lk5PIQXQPj575spW9WW01BONst2IOc',
//     'charset' => 'utf8mb4'
// ]);
