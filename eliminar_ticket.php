<?php
session_start();

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

require 'vendor/autoload.php';

// Configuración de Google Client
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

    foreach ($rows as $index => $ticket) {
        if ($ticket[0] == $ticketId) {
            $range = "Hoja1!A" . ($index + 2); 
            $requests = [
                new Google_Service_Sheets_Request([
                    'deleteDimension' => [
                        'range' => [
                            'sheetId' => 0, 
                            'dimension' => 'ROWS',
                            'startIndex' => $index + 1,
                            'endIndex' => $index + 2,
                        ]
                    ]
                ])
            ];
            $batchUpdateRequest = new Google_Service_Sheets_BatchUpdateSpreadsheetRequest(['requests' => $requests]);
            $service->spreadsheets->batchUpdate($spreadsheetId, $batchUpdateRequest);

            header('Location: consultar_tickets.php');
            exit;
        }
    }
}

header('Location: consultar_tickets.php');
exit;
?>