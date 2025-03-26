<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require 'vendor/autoload.php';

$client = new Google_Client();
$client->setApplicationName('Tickets');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
$client->setAuthConfig('tickets-454716-2891c097b536.json');
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '1Gn02KrETrvGb5sn_yahl9xr6FhyjqhQIFDbtPmBrSF0'; 

$ticketId = $_GET['id'] ?? null;

if ($ticketId) {

    $response = $service->spreadsheets_values->get($spreadsheetId, 'Hoja1');
    $rows = $response->getValues();

    if (!empty($rows)) {
        array_shift($rows);
    }

    foreach ($rows as $ticket) {
        if ($ticket[0] == $ticketId) {
            $ticketData = [
                'id' => $ticket[0],
                'ticket' => $ticket[1],
                'usuario' => $ticket[2],
                'persona_encargada' => $ticket[3],
                'departamento' => $ticket[4],
                'tipo_actividad' => $ticket[5],
                'detalle' => $ticket[6],
                'fecha_inicio' => $ticket[7],
                'estado' => $ticket[8],
                'fecha_finalizacion' => $ticket[9],
                'solucion' => $ticket[10],
                'tipo_ticket' => isset($ticket[12]) ? $ticket[12] : 'No especificado', 
            ];
            break;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Detalle del Ticket</title>
</head>
<body class="bg-gray-200">

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Detalle del Ticket</h1>
        
        <?php if (isset($ticketData)): ?>
            <div class="bg-white p-4 rounded shadow">
                <p><strong>ID:</strong> <?= htmlspecialchars($ticketData['id']) ?></p>
                <p><strong>Ticket:</strong> <?= htmlspecialchars($ticketData['ticket']) ?></p>
                <p><strong>Usuario:</strong> <?= htmlspecialchars($ticketData['usuario']) ?></p>
                <p><strong>Persona Encargada:</strong> <?= htmlspecialchars($ticketData['persona_encargada']) ?></p>
                <p><strong>Departamento:</strong> <?= htmlspecialchars($ticketData['departamento']) ?></p>
                <p><strong>Tipo de Actividad:</strong> <?= htmlspecialchars($ticketData['tipo_actividad']) ?></p>
                <p><strong>Detalle:</strong> <?= htmlspecialchars($ticketData['detalle']) ?></p>
                <p><strong>Fecha de Inicio:</strong> <?= htmlspecialchars($ticketData['fecha_inicio']) ?></p>
                <p><strong>Estado:</strong> <?= htmlspecialchars($ticketData['estado']) ?></p>
                <p><strong>Fecha de Finalización:</strong> <?= htmlspecialchars($ticketData['fecha_finalizacion']) ?></p>
                <p><strong>Solución:</strong> <?= htmlspecialchars($ticketData['solucion']) ?></p>
                <p><strong>Tipo de Ticket:</strong> <?= htmlspecialchars($ticketData['tipo_ticket']) ?></p> 
            </div>
        <?php else: ?>
            <p class="text-red-500">No se encontró el ticket.</p>
        <?php endif; ?>

        <a href="consultar_tickets.php" class="mt-4 inline-block text-blue-600">Volver a Consultar Tickets</a>
    </div>
</body>
</html>