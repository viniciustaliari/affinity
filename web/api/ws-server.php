<?php
require __DIR__ . '/../vendor/autoload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;
use Ratchet\RFC6455\Messaging\Frame;

class ProgramaWs implements MessageComponentInterface
{
    private \SplObjectStorage $uiConnections;
    private \SplObjectStorage $clientConnections;
    private \SplObjectStorage $packageReceiverConnections;
    private array $connectionRoleById = [];
    private array $contextoByConnectionId = [];
    private array $clientInfoByConnectionId = [];

    public function __construct()
    {
        $this->uiConnections = new \SplObjectStorage();
        $this->clientConnections = new \SplObjectStorage();
        $this->packageReceiverConnections = new \SplObjectStorage();
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $qs);

        $role = strtolower(trim((string)($qs['role'] ?? 'client')));
        if ($role === 'ui') {
            $this->connectionRoleById[$conn->resourceId] = 'ui';
            $this->uiConnections->attach($conn);

            $uiContexto = trim((string)($qs['contexto'] ?? ""));
            $receivePackages = $this->toBool($qs['receive_packages'] ?? null) || $uiContexto !== '';
            if ($receivePackages) {
                $this->packageReceiverConnections->attach($conn);
            }

            $conn->send($this->toJson([
                "type" => "ui_connected",
                "status" => "ok",
                "receive_packages" => $receivePackages
            ]));
            $this->sendClientsSnapshot($conn);
            return;
        }

        $contextoRaw = trim((string)($qs['contexto'] ?? ""));
        $contexto = $contextoRaw !== "" ? $contextoRaw : null;

        $peer = $this->extractPeer($conn);
        $clientInfo = [
            "connectionId" => (int)$conn->resourceId,
            "ip" => $peer["ip"],
            "port" => $peer["port"],
            "rawAddress" => $peer["rawAddress"],
            "contexto" => $contexto,
            "connectedAt" => gmdate('c')
        ];

        $this->connectionRoleById[$conn->resourceId] = 'client';
        $this->contextoByConnectionId[$conn->resourceId] = (string)($contexto ?? '');
        $this->clientInfoByConnectionId[$conn->resourceId] = $clientInfo;
        $this->clientConnections->attach($conn);
        $this->packageReceiverConnections->attach($conn);

        $this->broadcastToUi([
            "type" => "client_connected",
            "client" => $clientInfo
        ]);

        if ($contexto !== null) {
            $this->sendProgramPayload($conn, $contexto);
        } else {
            $conn->send($this->toJson([
                "type" => "client_connected_ack",
                "status" => "ok",
                "client" => $clientInfo
            ]));
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $role = $this->connectionRoleById[$from->resourceId] ?? 'client';
        $data = json_decode((string)$msg, true);

        if (!is_array($data)) {
            $from->send($this->toJson([
                "type" => "error",
                "error" => "Invalid JSON message."
            ]));
            return;
        }

        $type = (string)($data["type"] ?? "");

        if ($role === 'ui') {
            if ($type === "get_clients") {
                $this->sendClientsSnapshot($from);
                return;
            }

            if ($type === "push_program") {
                $programId = intval($data["program_id"] ?? 0);
                if ($programId <= 0) {
                    $from->send($this->toJson([
                        "type" => "push_program_result",
                        "status" => "error",
                        "error" => "Missing or invalid program_id."
                    ]));
                    return;
                }

                $package = $this->loadProgramPackageByIdSafe($programId);
                if (isset($package["error"])) {
                    $from->send($this->toJson([
                        "type" => "push_program_result",
                        "status" => "error",
                        "error" => $package["error"],
                        "program_id" => $programId
                    ]));
                    return;
                }

                $broadcastResult = $this->broadcastPackageToClients($package);
                if (!empty($broadcastResult["error"])) {
                    $from->send($this->toJson([
                        "type" => "push_program_result",
                        "status" => "error",
                        "error" => (string)$broadcastResult["error"],
                        "program_id" => $programId
                    ]));
                    return;
                }
                $sent = (int)($broadcastResult["sent"] ?? 0);

                $result = [
                    "type" => "push_program_result",
                    "status" => "ok",
                    "program_id" => (int)($package["program_id"] ?? $programId),
                    "program_name" => (string)($package["program_name"] ?? ""),
                    "contexto" => (string)($package["contexto"] ?? ""),
                    "zip_name" => (string)($package["zip_name"] ?? ""),
                    "zip_size_bytes" => (int)($package["zip_size_bytes"] ?? 0),
                    "missing_files" => $package["missing_files"] ?? [],
                    "receivers_connected" => count($this->packageReceiverConnections),
                    "clients_sent" => $sent
                ];

                $from->send($this->toJson($result));
                $this->broadcastToUi(array_merge($result, ["type" => "program_pushed"]));
                return;
            }

            $from->send($this->toJson([
                "type" => "error",
                "error" => "Unknown UI message. Use get_clients or push_program."
            ]));
            return;
        }

        if ($type === "refresh") {
            $contexto = $this->contextoByConnectionId[$from->resourceId] ?? '';
            if (trim($contexto) === '') {
                $from->send($this->toJson([
                    "type" => "error",
                    "error" => "No context set. Use ?contexto=1 at connect or send {\"type\":\"set_context\",\"contexto\":\"1\"}."
                ]));
                return;
            }

            $this->sendProgramPayload($from, $contexto);
            return;
        }

        if ($type === "set_context") {
            $newContexto = trim((string)($data["contexto"] ?? ""));
            if ($newContexto === '') {
                $from->send($this->toJson([
                    "type" => "error",
                    "error" => "Missing 'contexto' in set_context message."
                ]));
                return;
            }

            $this->contextoByConnectionId[$from->resourceId] = $newContexto;
            if (isset($this->clientInfoByConnectionId[$from->resourceId])) {
                $this->clientInfoByConnectionId[$from->resourceId]["contexto"] = $newContexto;
                $this->broadcastToUi([
                    "type" => "client_updated",
                    "client" => $this->clientInfoByConnectionId[$from->resourceId]
                ]);
            }

            $from->send($this->toJson([
                "type" => "context_set",
                "status" => "ok",
                "contexto" => $newContexto
            ]));

            $this->sendProgramPayload($from, $newContexto);
            return;
        }

        $from->send($this->toJson([
            "type" => "error",
            "error" => "Unknown message. Use refresh or set_context."
        ]));
    }

    public function onClose(ConnectionInterface $conn)
    {
        $role = $this->connectionRoleById[$conn->resourceId] ?? null;

        if ($role === 'ui' && $this->uiConnections->contains($conn)) {
            $this->uiConnections->detach($conn);
        }

        if ($role === 'client' && $this->clientConnections->contains($conn)) {
            $this->clientConnections->detach($conn);
        }
        if ($this->packageReceiverConnections->contains($conn)) {
            $this->packageReceiverConnections->detach($conn);
        }

        if ($role === 'client' && isset($this->clientInfoByConnectionId[$conn->resourceId])) {
            $clientInfo = $this->clientInfoByConnectionId[$conn->resourceId];
            $this->broadcastToUi([
                "type" => "client_disconnected",
                "client" => $clientInfo
            ]);
        }

        unset($this->connectionRoleById[$conn->resourceId]);
        unset($this->contextoByConnectionId[$conn->resourceId]);
        unset($this->clientInfoByConnectionId[$conn->resourceId]);
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }

    private function sendClientsSnapshot(ConnectionInterface $conn): void
    {
        $conn->send($this->toJson([
            "type" => "clients_snapshot",
            "clients" => array_values($this->clientInfoByConnectionId)
        ]));
    }

    private function broadcastToUi(array $message): void
    {
        $json = $this->toJson($message);
        foreach ($this->uiConnections as $uiConn) {
            $uiConn->send($json);
        }
    }

    private function toJson(array $data): string
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }

    private function sendProgramPayload(ConnectionInterface $conn, string $contexto): void
    {
        $payload = $this->buildProgramPayloadSafe($contexto);
        $conn->send($this->toJson($payload));
    }

    private function buildProgramPayloadSafe(string $contexto): array
    {
        try {
            require_once __DIR__ . '/programa_payload.php';

            if (!function_exists('buildProgramaPayload')) {
                return [
                    "type" => "error",
                    "error" => "Payload builder not available."
                ];
            }

            return buildProgramaPayload($contexto);
        } catch (\Throwable $e) {
            return [
                "type" => "error",
                "error" => "No se pudo generar payload del programa.",
                "contexto" => $contexto
            ];
        }
    }

    private function loadProgramPackageByIdSafe(int $programId): array
    {
        try {
            require_once __DIR__ . '/programa_package.php';

            if (!function_exists('getOrBuildProgramaZipSnapshotFromDatabase')) {
                return [
                    "type" => "error",
                    "error" => "Program package builder is not available."
                ];
            }

            $result = getOrBuildProgramaZipSnapshotFromDatabase($programId);
            if (($result["status"] ?? "") !== "ok") {
                return [
                    "type" => "error",
                    "error" => (string)($result["error"] ?? "Could not build program package.")
                ];
            }

            return $result;
        } catch (\Throwable $e) {
            return [
                "type" => "error",
                "error" => "No se pudo generar el paquete zip para el programa.",
                "program_id" => $programId
            ];
        }
    }

    private function broadcastPackageToClients(array $package): array
    {
        $zipBase64 = (string)($package["zip_base64"] ?? "");
        $zipBinary = base64_decode($zipBase64, true);
        if ($zipBinary === false || $zipBinary === "") {
            return [
                "sent" => 0,
                "error" => "Invalid zip payload to broadcast."
            ];
        }

        $json = $this->toJson([
            "type" => "program_package",
            "program_id" => (int)($package["program_id"] ?? 0),
            "program_name" => (string)($package["program_name"] ?? ""),
            "contexto" => (string)($package["contexto"] ?? ""),
            "zip_name" => (string)($package["zip_name"] ?? "program.zip"),
            "zip_encoding" => "binary",
            "zip_size_bytes" => strlen($zipBinary),
            "has_binary_payload" => true,
            "manifest_file" => "program.json",
            "manifest" => $package["manifest"] ?? [],
            "missing_files" => $package["missing_files"] ?? [],
            "timestamp" => (int)round(microtime(true) * 1000)
        ]);
        $count = 0;

        foreach ($this->packageReceiverConnections as $clientConn) {
            try {
                $clientConn->send($json);
                $clientConn->send(new Frame($zipBinary, true, Frame::OP_BINARY));
                $count++;
            } catch (\Throwable $e) {
                // Ignoramos fallo individual y seguimos con el resto.
            }
        }

        return [
            "sent" => $count
        ];
    }

    private function extractPeer(ConnectionInterface $conn): array
    {
        $ip = 'unknown';
        $port = null;
        $request = $conn->httpRequest ?? null;
        $rawAddress = null;

        if ($request && method_exists($request, 'getServerParams')) {
            $params = (array)$request->getServerParams();

            $forwardedFor = isset($params['HTTP_X_FORWARDED_FOR']) ? (string)$params['HTTP_X_FORWARDED_FOR'] : '';
            if ($forwardedFor !== '') {
                $firstIp = trim(explode(',', $forwardedFor)[0]);
                if ($firstIp !== '') {
                    $ip = trim($firstIp, '[]');
                }
            }

            if ($ip === 'unknown' && !empty($params['REMOTE_ADDR'])) {
                $ip = trim((string)$params['REMOTE_ADDR'], '[]');
            }

            if (!empty($params['REMOTE_PORT'])) {
                $port = (int)$params['REMOTE_PORT'];
            }
        }

        if ($ip === 'unknown' || $port === null) {
            $candidates = $this->collectRemoteCandidates($conn);
            foreach ($candidates as $candidate) {
                if ($rawAddress === null) {
                    $rawAddress = $candidate;
                }

                $parsed = $this->parseAddress($candidate);
                if ($ip === 'unknown' && $parsed['ip'] !== null) {
                    $ip = $parsed['ip'];
                }
                if ($port === null && $parsed['port'] !== null) {
                    $port = $parsed['port'];
                }
                if ($ip !== 'unknown' && $port !== null) {
                    break;
                }
            }
        }

        if ($request && method_exists($request, 'getUri')) {
            $uri = $request->getUri();
            if ($uri && method_exists($uri, 'getHost') && $ip === 'unknown') {
                $host = trim((string)$uri->getHost());
                if ($host !== '') {
                    $ip = $host;
                }
            }
            if ($uri && method_exists($uri, 'getPort') && $port === null) {
                $uriPort = $uri->getPort();
                if ($uriPort !== null) {
                    $port = (int)$uriPort;
                }
            }
        }

        return [
            "ip" => $ip,
            "port" => $port,
            "rawAddress" => $rawAddress
        ];
    }

    private function collectRemoteCandidates(object $conn): array
    {
        $candidates = [];
        $current = $conn;
        $seen = [];
        $depth = 0;

        while (is_object($current) && $depth < 10) {
            $oid = spl_object_id($current);
            if (isset($seen[$oid])) {
                break;
            }
            $seen[$oid] = true;
            $depth++;

            try {
                $value = (string)($current->remoteAddress ?? '');
                if ($value !== '') {
                    $candidates[] = $value;
                }
            } catch (\Throwable $e) {
            }

            if (method_exists($current, 'getRemoteAddress')) {
                try {
                    $value = (string)$current->getRemoteAddress();
                    if ($value !== '') {
                        $candidates[] = $value;
                    }
                } catch (\Throwable $e) {
                }
            }

            $next = $this->readHiddenProperty($current, 'wrappedConn');
            if (is_object($next)) {
                $current = $next;
                continue;
            }

            $next = $this->readHiddenProperty($current, 'conn');
            if (is_object($next)) {
                $current = $next;
                continue;
            }

            break;
        }

        return array_values(array_unique(array_filter($candidates, static function ($x) {
            return trim((string)$x) !== '';
        })));
    }

    private function readHiddenProperty(object $obj, string $name)
    {
        try {
            $ref = new \ReflectionObject($obj);
            while ($ref) {
                if ($ref->hasProperty($name)) {
                    $prop = $ref->getProperty($name);
                    $prop->setAccessible(true);
                    return $prop->getValue($obj);
                }
                $ref = $ref->getParentClass();
            }
        } catch (\Throwable $e) {
        }

        return null;
    }

    private function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return intval($value) === 1;
        }

        $v = strtolower(trim((string)$value));
        return in_array($v, ['1', 'true', 'yes', 'on'], true);
    }

    private function parseAddress(string $raw): array
    {
        $raw = trim($raw);
        if ($raw === '') {
            return ["ip" => null, "port" => null];
        }

        if (preg_match('/^(?:\w+:\/\/)?\[(.+)\]:(\d+)$/', $raw, $m)) {
            return ["ip" => trim((string)$m[1]), "port" => (int)$m[2]];
        }

        if (preg_match('/^(?:\w+:\/\/)?([^:\/]+):(\d+)$/', $raw, $m)) {
            return ["ip" => trim((string)$m[1]), "port" => (int)$m[2]];
        }

        if (preg_match('/^(?:\w+:\/\/)?(.+)$/', $raw, $m)) {
            return ["ip" => trim((string)$m[1], '[]'), "port" => null];
        }

        return ["ip" => null, "port" => null];
    }
}

$port = 8090;

$server = IoServer::factory(
    new HttpServer(new WsServer(new ProgramaWs())),
    $port,
    "0.0.0.0"
);

echo "WebSocket server running on 0.0.0.0:{$port}\n";
$server->run();
