<?php
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/programa_payload.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\Server\IoServer;

class ProgramaWs implements MessageComponentInterface
{
    public function onOpen(ConnectionInterface $conn)
    {
        // 1) Leemos el query string del URL (ej: ?contexto=standby)
        $query = $conn->httpRequest->getUri()->getQuery();
        parse_str($query, $qs);
        $contexto = isset($qs['contexto']) ? (string)$qs['contexto'] : "";

        // 2) Construimos el payload igual que el endpoint HTTP
        $payload = buildProgramaPayload($contexto);

        // 3) Lo enviamos como JSON
        $conn->send(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        // 4) Si era error, cerramos la conexión (opcional, pero práctico)
        if (isset($payload["error"])) {
            $conn->close();
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        // opcional: permitir refrescar si el cliente lo pide
        $data = json_decode((string)$msg, true);

        if (is_array($data) && ($data["type"] ?? "") === "refresh") {
            $query = $from->httpRequest->getUri()->getQuery();
            parse_str($query, $qs);
            $contexto = isset($qs['contexto']) ? (string)$qs['contexto'] : "";

            $payload = buildProgramaPayload($contexto);
            $from->send(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return;
        }

        // Si te mandan otra cosa:
        $from->send(json_encode([
            "type" => "error",
            "error" => "Unknown message. Send {\"type\":\"refresh\"} to reload."
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    }

    public function onClose(ConnectionInterface $conn) { }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        $conn->close();
    }
}

$port = 8090;

$server = IoServer::factory(
    new HttpServer(new WsServer(new ProgramaWs())),
    $port
);

echo "WebSocket server running on port {$port}\n";
$server->run();
