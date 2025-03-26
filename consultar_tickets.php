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

$response = $service->spreadsheets_values->get($spreadsheetId, 'Hoja1');
$rows = $response->getValues();

if (!empty($rows)) {
    array_shift($rows);
}

$estadoFiltro = $_GET['estado'] ?? null;
$tickets = [];
foreach ($rows as $ticket) {
    $estado = $ticket[8]; 
    if ($estadoFiltro === null || $estadoFiltro === '' || $estado === $estadoFiltro) {
        $tickets[] = [
            'id' => $ticket[0], 
            'ticket' => $ticket[1],
            'tipo_actividad' => $ticket[5],
            'tipo_ticket' => $ticket[12], 
            'fecha_inicio' => $ticket[7], 
            'estado' => $estado,
            'fecha_finalizacion' => $ticket[9],
        ];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title>Consultar Tickets</title>
    <script>
        function openModal(ticketId) {
            document.getElementById('ticket-id').innerText = ticketId;
            document.getElementById('modal').classList.remove('hidden');
            document.getElementById('delete-ticket-id').value = ticketId;
        }

        function closeModal() {
            document.getElementById('modal').classList.add('hidden');
        }

        function confirmDelete() {
            const password = document.getElementById('password').value;
            if (password === 'Soporte2025') {
                document.getElementById('delete-form').submit();
            } else {
                alert('Contraseña incorrecta. Inténtalo de nuevo.');
            }
        }
    </script>
</head>
<body class="bg-gray-200">

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
            <h2 class="text-white">Menú</h2>
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
        <h1 class="text-2xl font-bold mb-4">Consultar Tickets</h1>

        <!-- Filtro de Estado -->
        <form method="GET" class="mb-4">
            <label for="estado" class="mr-2">Filtrar por estado:</label>
            <select name="estado" id="estado" class="border rounded p-2">
                <option value="">Todos</option>
                <option value="En curso" <?= (isset($estadoFiltro) && $estadoFiltro === "En curso") ? 'selected' : '' ?>>En curso</option>
                <option value="Cerrado" <?= (isset($estadoFiltro) && $estadoFiltro === "Cerrado") ? 'selected' : '' ?>>Cerrado</option>
            </select>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Filtrar</button>
        </form>

        <table class="min-w-full bg-white border border-gray-300">
            <thead>
                <tr>
                    <th class="border px-4 py-2">Ticket</th>
                    <th class="border px-4 py-2">Tipo Actividad</th>
                    <th class="border px-4 py-2">Tipo Ticket</th> 
                    <th class="border px-4 py-2">Fecha Inicio</th>
                    <th class="border px-4 py-2">Estado</th>
                    <th class="border px-4 py-2">Fecha Finalización</th>
                    <th class="border px-4 py-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tickets as $ticket): ?>
                <tr>
                    <td class="border px-4 py-2"><?= htmlspecialchars($ticket['ticket']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($ticket['tipo_actividad']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($ticket['tipo_ticket']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($ticket['fecha_inicio']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($ticket['estado']) ?></td>
                    <td class="border px-4 py-2"><?= htmlspecialchars($ticket['fecha_finalizacion']) ?></td>
                    <td class="border px-4 py-2">
                        <a href="detalle_ticket.php?id=<?= htmlspecialchars($ticket['id']) ?>" class="text-blue-600 bg-blue-200 px-2 py-1 rounded">Detalle</a>
                        <?php if ($ticket['estado'] === 'Cerrado'): ?>
                            <span class="text-gray-600">Finalizado</span>
                        <?php else: ?>
                            <a href="editar_ticket.php?id=<?= htmlspecialchars($ticket['id']) ?>" class="text-blue-600 bg-blue-200 px-2 py-1 rounded">Editar</a>
                            <form method="POST" action="finalizar_ticket.php" class="inline">
                                <input type="hidden" name="id" value="<?= htmlspecialchars($ticket['id']) ?>">
                                <button type="submit" class="text-green-600 bg-green-200 px-2 py-1 rounded">Finalizar</button>
                            </form>
                            <button onclick="openModal('<?= htmlspecialchars($ticket['id']) ?>')" class="text-red-600 bg-red-200 px-2 py-1 rounded">Eliminar</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div id="modal" class="fixed inset-0 bg-gray-500 bg-opacity-75 z-50 hidden flex items-center justify-center">
        <div class="bg-white p-4 rounded shadow-lg w-1/3">
            <h2 class="text-lg font-bold mb-2">Confirmar Eliminación</h2>
            <p>¿Estás seguro de eliminar el ticket: <span id="ticket-id" class="font-semibold"></span>?</p>
            <input type="password" id="password" placeholder="Ingresa la contraseña" class="border rounded p-2 mt-2 w-full">
            <form id="delete-form" method="POST" action="eliminar_ticket.php" class="hidden">
                <input type="hidden" name="id" id="delete-ticket-id" value="">
            </form>
            <div class="mt-4">
                <button onclick="confirmDelete()" class="bg-red-600 text-white px-4 py-2 rounded">Confirmar</button>
                <button onclick="closeModal()" class="bg-gray-300 text-gray-700 px-4 py-2 rounded">Cancelar</button>
            </div>
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