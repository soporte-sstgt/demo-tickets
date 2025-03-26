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

$ticketId = $_POST['id'] ?? null;

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
                'tipo_actividad' => $ticket[5],
                'fecha_inicio' => $ticket[7],
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
    <title>Finalizar Ticket</title>
</head>
<body class="bg-gray-200">

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Finalizar Ticket</h1>
        
        <?php if (isset($ticketData)): ?>
            <form method="POST" action="guardar_finalizacion.php">
                <input type="hidden" name="ticket_id" value="<?= htmlspecialchars($ticketData['id']) ?>">

                <div class="mb-4">
                    <label class="block text-gray-700">Ticket</label>
                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md" value="<?= htmlspecialchars($ticketData['ticket']) ?>" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Tipo Actividad</label>
                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md" value="<?= htmlspecialchars($ticketData['tipo_actividad']) ?>" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Fecha de Inicio</label>
                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md" value="<?= htmlspecialchars($ticketData['fecha_inicio']) ?>" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Fecha de Finalización</label>
                    <input type="text" class="mt-1 block w-full border-gray-300 rounded-md" value="<?= date('Y-m-d') ?>" readonly>
                </div>
                <div class="mb-4">
                    <label class="block text-gray-700">Solución</label>
                    <textarea name="solucion" class="mt-1 block w-full border-gray-300 rounded-md" required></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Guardar</button>
            </form>
        <?php else: ?>
            <p class="text-red-500">No se encontró el ticket.</p>
        <?php endif; ?>
    </div>
</body>
</html>