<?php
require_once 'config.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['user'])) {
    die(json_encode(['success' => false, 'message' => 'No autorizado']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'registrar':
            try {
                $conn->begin_transaction();
                
                $prestamoId = $conn->real_escape_string($_POST['prestamoId']);
                $monto = $conn->real_escape_string($_POST['monto']);
                $tipo = $conn->real_escape_string($_POST['tipo']);
                $fecha = $conn->real_escape_string($_POST['fecha']);
                $numeroCuota = isset($_POST['numeroCuota']) ? $conn->real_escape_string($_POST['numeroCuota']) : null;
                
                // Registrar pago
                $sql = "INSERT INTO pagos (prestamo_id, monto, tipo, fecha, numero_cuota) 
                        VALUES ('$prestamoId', '$monto', '$tipo', '$fecha', " . ($numeroCuota ? "'$numeroCuota'" : "NULL") . ")";
                
                if (!$conn->query($sql)) {
                    throw new Exception("Error al registrar el pago");
                }
                
                $pagoId = $conn->insert_id;
                
                // Si es pago total, actualizar estado del préstamo
                if ($tipo === 'pago total') {
                    $sqlUpdate = "UPDATE prestamos SET estado = 'Pagado' WHERE id = '$prestamoId'";
                    if (!$conn->query($sqlUpdate)) {
                        throw new Exception("Error al actualizar el estado del préstamo");
                    }
                }
                
                $conn->commit();
                
                // Obtener información detallada del pago
                $sqlPago = "SELECT p.*, pr.cliente_id, c.nombre as cliente_nombre 
                           FROM pagos p 
                           JOIN prestamos pr ON p.prestamo_id = pr.id
                           JOIN clientes c ON pr.cliente_id = c.id
                           WHERE p.id = '$pagoId'";
                           
                $result = $conn->query($sqlPago);
                $pagoInfo = $result->fetch_assoc();
                
                echo json_encode([
                    'success' => true,
                    'message' => 'Pago registrado correctamente',
                    'pago' => $pagoInfo
                ]);
                
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
            }
            break;
            
        case 'obtener':
            // Obtener todos los pagos con información del cliente y préstamo
            $sql = "SELECT p.*, pr.cliente_id, c.nombre as cliente_nombre,
                   pr.monto as prestamo_monto, pr.estado as prestamo_estado
                   FROM pagos p 
                   JOIN prestamos pr ON p.prestamo_id = pr.id
                   JOIN clientes c ON pr.cliente_id = c.id
                   ORDER BY p.fecha DESC";
            
            $result = $conn->query($sql);
            $pagos = [];
            
            while ($row = $result->fetch_assoc()) {
                $pagos[] = [
                    'id' => intval($row['id']),
                    'prestamoId' => intval($row['prestamo_id']),
                    'cliente' => $row['cliente_nombre'],
                    'monto' => floatval($row['monto']),
                    'tipo' => $row['tipo'],
                    'fecha' => $row['fecha'],
                    'numeroCuota' => $row['numero_cuota']
                ];
            }
            
            echo json_encode(['success' => true, 'pagos' => $pagos]);
            break;
            
        case 'prestamos_activos':
            // Obtener préstamos activos para el selector de pagos
            $sql = "SELECT p.*, c.nombre as cliente_nombre
                   FROM prestamos p 
                   JOIN clientes c ON p.cliente_id = c.id
                   WHERE p.estado = 'Activo'
                   ORDER BY p.fecha_inicio DESC";
            
            $result = $conn->query($sql);
            $prestamos = [];
            
            while ($row = $result->fetch_assoc()) {
                $prestamos[] = [
                    'id' => intval($row['id']),
                    'cliente' => $row['cliente_nombre'],
                    'monto' => floatval($row['monto']),
                    'cuota' => floatval($row['cuota']),
                    'plazo' => intval($row['plazo']),
                    'frecuencia' => $row['frecuencia'],
                    'fechaInicio' => $row['fecha_inicio']
                ];
            }
            
            echo json_encode(['success' => true, 'prestamos' => $prestamos]);
            break;
    }
}
?>