<?php

require 'vendor/autoload.php'; 

$client = new Google_Client();
$client->setApplicationName('Tickets');
$client->setScopes(Google_Service_Sheets::SPREADSHEETS);
$client->setAuthConfig('tickets-454716-2891c097b536.json');
$client->setAccessType('offline');

$service = new Google_Service_Sheets($client);
$spreadsheetId = '1Gn02KrETrvGb5sn_yahl9xr6FhyjqhQIFDbtPmBrSF0'; // ID HOJA CALCULO

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ticketData = [
        'ticket' => $_POST['ticket'],
        'id_estacion' => $_POST['id_estacion'],
        'usuario' => $_POST['usuario'],
        'persona_encargada' => $_POST['persona_encargada'],
        'departamento' => $_POST['departamento'],
        'tipo_actividad' => $_POST['tipo_actividad'],
        'detalle' => $_POST['detalle'],
        'fecha_inicio' => $_POST['fecha_inicio'],
        'estado' => 'En curso', 
        'fecha_finalizacion' => '',
        'solucion' => '', 
        'comentarios' => $_POST['comentarios'],
        'tipo_ticket' => $_POST['tipo_ticket'], 
    ];

    
    $dataToInsert = [
        $ticketData['ticket'],            
        $ticketData['id_estacion'],       
        $ticketData['usuario'],           
        $ticketData['persona_encargada'], 
        $ticketData['departamento'],      
        $ticketData['tipo_actividad'],    
        $ticketData['detalle'],           
        $ticketData['fecha_inicio'],      
        $ticketData['estado'],            
        $ticketData['fecha_finalizacion'], 
        $ticketData['solucion'],          
        $ticketData['comentarios'],       
        $ticketData['tipo_ticket'],       
    ];

    foreach ($dataToInsert as $key => $value) {
        if (empty($value) && $key != 9 && $key != 10) { 
            die('Error: uno o más campos están vacíos.');
        }
    }

    
    $body = new Google_Service_Sheets_ValueRange(['values' => [$dataToInsert]]);
    $result = $service->spreadsheets_values->append($spreadsheetId, 'Hoja1', $body, ['valueInputOption' => 'RAW']);
    
    
    header('Location: consultar_tickets.php');
    exit();
}


$tipoTicket = isset($_GET['tipo']) ? $_GET['tipo'] : '';


$titulo = 'Agregar Nuevo Ticket';
if ($tipoTicket) {
    switch ($tipoTicket) {
        case 'soporte':
            $titulo = 'Agregar Nuevo Ticket de Soporte';
            break;
        case 'mantenimiento':
            $titulo = 'Agregar Nuevo Ticket de Mantenimiento';
            break;
        case 'incidencia':
            $titulo = 'Agregar Nuevo Ticket de Incidencia';
            break;
        case 'interno':
            $titulo = 'Agregar Nuevo Ticket Interno';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <title><?= htmlspecialchars($titulo) ?></title>
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
            <a href="consultar_tickets.php" class="text-white mx-2 hidden lg:block">Consultar Tickets</a>
            <a href="logout.php" class="text-white mx-2 hidden lg:block">Cerrar Sesión</a>
        </div>
        <button id="menu-toggle" class="text-white lg:hidden focus:outline-none">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"></path>
            </svg>
        </button>
    </nav>

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
            <a href="consultar_tickets.php" class="text-white py-2">Consultar Tickets</a>
            <a href="logout.php" class="text-white py-2">Cerrar Sesión</a>
        </div>
    </div>

    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4 text-center"><?= htmlspecialchars($titulo) ?></h1>
        <div class="form-container">
            <form method="POST" id="ticketForm">
                <div class="mb-4">
                    <label class="input-label">Ticket</label>
                    <input type="text" name="ticket" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">ID Estación de Trabajo</label>
                    <input type="text" name="id_estacion" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Usuario</label>
                    <input type="text" name="usuario" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Persona encargada</label>
                    <input type="text" name="persona_encargada" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Departamento</label>
                    <select name="departamento" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                        <option value="" disabled selected>Seleccione un departamento</option>
                        <option value="Administracion">Administración</option>
                        <option value="Contabilidad">Contabilidad</option>
                        <option value="Mercadeo">Mercadeo</option>
                        <option value="Diseño">Diseño</option>
                        <option value="Bodega">Bodega</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="input-label">Tipo Actividad</label>
                    <input type="text" name="tipo_actividad" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Detalle</label>
                    <textarea name="detalle" class="mt-1 block w-full border border-gray-300 rounded-md input-field" required></textarea>
                </div>
                <div class="mb-4">
                    <label class="input-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="mt-1 block w-full border border-gray-300 rounded-md input-field" value="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="mb-4">
                    <label class="input-label">Comentarios Adicionales</label>
                    <textarea name="comentarios" class="mt-1 block w-full border border-gray-300 rounded-md input-field"></textarea>
                </div>
                <input type="hidden" name="tipo_ticket" value="<?= htmlspecialchars($tipoTicket) ?>"> 
                <button type="submit" class="mt-4 submit-button text-white px-4 py-2 rounded">Agregar Ticket</button>
            </form>
        </div>
    </div>

    <script>
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