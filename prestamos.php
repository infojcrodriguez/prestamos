<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'registrar':
            $clienteId = $conn->real_escape_string($_POST['clienteId']);
            $prestamistaId = $conn->real_escape_string($_POST['prestamistaId']);
            $monto = $conn->real_escape_string($_POST['monto']);
            $interes = $conn->real_escape_string($_POST['interes']);
            $frecuencia = $conn->real_escape_string($_POST['frecuencia']);
            $plazo = $conn->real_escape_string($_POST['plazo']);
            $cuota = $conn->real_escape_string($_POST['cuota']);
            
            $sql = "INSERT INTO prestamos (cliente_id, prestamista_id, monto, interes, frecuencia, plazo, cuota) 
                    VALUES ('$clienteId', '$prestamistaId', '$monto', '$interes', '$frecuencia', '$plazo', '$cuota')";
            
            if ($conn->query($sql)) {
                $prestamoId = $conn->insert_id;
                
                // Obtener datos adicionales para la respuesta
                $sqlInfoExtra = "SELECT p.*, c.nombre as cliente_nombre, pr.nombre as prestamista_nombre 
                               FROM prestamos p 
                               JOIN clientes c ON p.cliente_id = c.id 
                               JOIN prestamistas pr ON p.prestamista_id = pr.id 
                               WHERE p.id = '$prestamoId'";
                $resultInfo = $conn->query($sqlInfoExtra);
                $infoExtra = $resultInfo->fetch_assoc();
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'Préstamo registrado correctamente',
                    'prestamo' => [
                        'id' => $prestamoId,
                        'cliente' => $infoExtra['cliente_nombre'],
                        'prestamistaNombre' => $infoExtra['prestamista_nombre'],
                        'monto' => floatval($infoExtra['monto']),
                        'interes' => floatval($infoExtra['interes']),
                        'frecuencia' => $infoExtra['frecuencia'],
                        'plazo' => intval($infoExtra['plazo']),
                        'cuota' => floatval($infoExtra['cuota']),
                        'estado' => $infoExtra['estado'],
                        'fechaInicio' => $infoExtra['fecha_inicio']
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el préstamo']);
            }
            break;
            
        case 'obtener':
            $sql = "SELECT p.*, c.nombre as cliente_nombre, pr.nombre as prestamista_nombre 
                   FROM prestamos p 
                   JOIN clientes c ON p.cliente_id = c.id 
                   JOIN prestamistas pr ON p.prestamista_id = pr.id 
                   ORDER BY p.fecha_inicio DESC";
            
            $result = $conn->query($sql);
            $prestamos = [];
            
            while ($row = $result->fetch_assoc()) {
                $prestamos[] = [
                    'id' => intval($row['id']),
                    'clienteId' => intval($row['cliente_id']),
                    'prestamistaId' => intval($row['prestamista_id']),
                    'cliente' => $row['cliente_nombre'],
                    'prestamistaNombre' => $row['prestamista_nombre'],
                    'monto' => floatval($row['monto']),
                    'interes' => floatval($row['interes']),
                    'frecuencia' => $row['frecuencia'],
                    'plazo' => intval($row['plazo']),
                    'cuota' => floatval($row['cuota']),
                    'estado' => $row['estado'],
                    'fechaInicio' => $row['fecha_inicio']
                ];
            }
            
            echo json_encode(['success' => true, 'prestamos' => $prestamos]);
            break;
    }
}
?>