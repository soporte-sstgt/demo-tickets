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

$ticket_id = $_GET['id'];
$response = $service->spreadsheets_values->get($spreadsheetId, 'Hoja1');
$rows = $response->getValues();
$ticket = null;

foreach ($rows as $row) {
    if ($row[0] === $ticket_id) {
        $ticket = $row;
        break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!$ticket) {
        exit('Ticket no encontrado.');
    }

    
    $ticket_data = [
        $ticket_id, 
        $_POST['id_estacion'], 
        $_POST['usuario'], 
        $_POST['persona_encargada'],
        $_POST['departamento'], 
        $_POST['tipo_actividad'],
        $_POST['detalle'], 
        $ticket[7], 
        $ticket[8], 
        '', 
        $_POST['comentarios'], 
    ];

    foreach ($rows as &$row) {
        if ($row[0] === $ticket_id) {
            $row = $ticket_data; 
            break;
        }
    }

    
    $body = new Google_Service_Sheets_ValueRange(['values' => $rows]);
    $result = $service->spreadsheets_values->update($spreadsheetId, 'Hoja1', $body, ['valueInputOption' => 'RAW']);

    header('Location: consultar_tickets.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Editar Ticket</title>
    <style>
        body {
            background-color: #f7fafc; 
        }
        .form-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }
        .input-field {
            transition: border-color 0.2s ease, box-shadow 0.2s ease;
        }
        .input-field:focus {
            border-color: #3182ce; 
            box-shadow: 0 0 5px rgba(49, 130, 206, 0.5);
        }
        .input-label {
            font-weight: bold;
            color: #4a5568; 
        }
        .submit-button {
            background-color: #3182ce;
            color: white;
            transition: background-color 0.2s ease;
        }
        .submit-button:hover {
            background-color: #2b6cb0;
        }
    </style>
</head>
<body>

<nav class="bg-gray-600 p-4 flex items-center justify-between">
        <div class="flex items-center">
            <img src="logosst.webp" alt="Logo" class="h-8 mr-4">
            <a href="dashboard.php" class="text-white mx-2 hidden lg:block">Dashboard</a>
            <a href="#" id="agregar-ticket" class="text-white mx-2 hidden lg:block">Agregar Ticket</a>
            <a href="consultar_tickets.php" class="text-white mx-2 hidden lg:block">Consultar Tickets</a>
            <a href="logout.php" class="text-white mx-2 hidden lg:block">Cerrar Sesión</a>
        </div>
        <button id="menu-toggle" class="text-white lg:hidden focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </nav>

    <div id="ticket-menu" class="fixed top-0 left-0 w-full h-full bg-gray-800 bg-opacity-50 hidden flex items-center justify-center">
        <div class="bg-white rounded p-4">
            <h2 class="font-bold mb-2">Seleccione una de las siguientes opciones</h2>
            <ul class="flex flex-col">
                <li><button class="ticket-option" data-type="soporte">Ticket de Soporte</button></li>
                <li><button class="ticket-option" data-type="mantenimiento">Ticket de Mantenimiento</button></li>
                <li><button class="ticket-option" data-type="incidencia">Ticket de Incidencia</button></li>
                <li><button class="ticket-option" data-type="interno">Ticket Interno</button></li>
            </ul>
            <button id="close-ticket-menu" class="mt-4 text-red-500">Cerrar</button>
        </div>
    </div>

    <div id="menu" class="fixed top-0 right-0 bg-gray-600 w-64 h-full transform translate-x-full transition-transform duration-300 lg:hidden" style="z-index: 100;">
        <div class="flex justify-between items-center p-4">
            <h2 class="text-white">Menu</h2>
            <button id="close-menu" class="text-white focus:outline-none">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        <div class="flex flex-col p-4">
            <a href="dashboard.php" class="text-white py-2">Dashboard</a>
            <a href="agregar_ticket.php" class="text-white py-2">Agregar Ticket</a>
            <a href="consultar_tickets.php" class="text-white py-2">Consultar Tickets</a>
            <a href="logout.php" class="text-white py-2">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Editar Ticket: <?= htmlspecialchars($ticket[0]) ?></h1>
        <div class="form-container">
            <form method="POST" id="ticketForm">
                <input type="hidden" name="ticket" value="<?= htmlspecialchars($ticket[0]) ?>" required>
                <div class="mb-4">
                    <label class="input-label">ID Estación de Trabajo</label>
                    <input type="text" name="id_estacion" value="<?= htmlspecialchars($ticket[1]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Usuario</label>
                    <input type="text" name="usuario" value="<?= htmlspecialchars($ticket[2]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Persona encargada</label>
                    <input type="text" name="persona_encargada" value="<?= htmlspecialchars($ticket[3]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Departamento</label>
                    <input type="text" name="departamento" value="<?= htmlspecialchars($ticket[4]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Tipo Actividad</label>
                    <input type="text" name="tipo_actividad" value="<?= htmlspecialchars($ticket[5]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Detalle</label>
                    <textarea name="detalle" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required><?= htmlspecialchars($ticket[6]) ?></textarea>
                </div>
                <div class="mb-4">
                    <label class="input-label">Fecha Inicio</label>
                    <input type="text" value="<?= htmlspecialchars($ticket[7]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" readonly required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Estado</label>
                    <input type="text" value="<?= htmlspecialchars($ticket[8]) ?>" class="mt-1 block w-full border border-gray-300 rounded-md input-field" readonly required>
                    <input type="hidden" name="estado" value="<?= htmlspecialchars($ticket[8]) ?>">
                </div>
                <button type="submit" class="mt-4 submit-button text-white px-4 py-2 rounded">Actualizar Ticket</button>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('agregar-ticket').addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById('ticket-menu').classList.remove('hidden');
        });

        document.getElementById('close-ticket-menu').addEventListener('click', function() {
            document.getElementById('ticket-menu').classList.add('hidden');
        });

        document.querySelectorAll('.ticket-option').forEach(function(button) {
            button.addEventListener('click', function() {
                const tipo = this.getAttribute('data-type');
                window.location.href = `agregar_ticket.php?tipo=${tipo}`;
            });
        });

        document.getElementById('menu-toggle').addEventListener('click', function() {
            const menu = document.getElementById('menu');
            menu.style.transform = 'translateX(0)';
        });

        document.getElementById('close-menu').addEventListener('click', function() {
            const menu = document.getElementById('menu');
            menu.style.transform = 'translateX(100%)';
        });
    </script>
</body>
</html>