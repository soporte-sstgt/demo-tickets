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

$ticketId = $_POST['ticket_id'] ?? null;
$solucion = $_POST['solucion'] ?? '';
$fecha_finalizacion = date('Y-m-d');

if ($ticketId) {

    $response = $service->spreadsheets_values->get($spreadsheetId, 'Hoja1');
    $rows = $response->getValues();

    foreach ($rows as $index => $row) {
        if ($row[0] == $ticketId) {
            $rowIndex = $index + 1;
            break;
        }
    }

    if (isset($rowIndex)) {
        $body = new Google_Service_Sheets_ValueRange(['values' => [[
            $ticketId, 
            $row[1], 
            $row[2], 
            $row[3], 
            $row[4], 
            $row[5], 
            $row[6], 
            $row[7], 
            'Cerrado',
            $fecha_finalizacion,
            $solucion,
        ]]]);
        
        $range = "Hoja1!A$rowIndex:K$rowIndex"; 
        $result = $service->spreadsheets_values->update($spreadsheetId, $range, $body, ['valueInputOption' => 'RAW']);

        header('Location: consultar_tickets.php');
        exit;
    }
}
?>