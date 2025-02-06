<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'estadisticas':
            // Total prestado y otras estadísticas
            $sql = "SELECT 
                    COALESCE(SUM(monto), 0) as total_prestado,
                    (SELECT COUNT(*) FROM clientes) as total_clientes,
                    (SELECT COUNT(*) FROM prestamos WHERE estado = 'Activo') as prestamos_activos,
                    (SELECT COALESCE(SUM(monto), 0) FROM pagos WHERE DATE(fecha) = CURDATE()) as pagos_hoy
                    FROM prestamos";
            
            $result = $conn->query($sql);
            $stats = $result->fetch_assoc();
            
            echo json_encode([
                'success' => true,
                'estadisticas' => [
                    'totalPrestado' => floatval($stats['total_prestado']),
                    'totalClientes' => intval($stats['total_clientes']),
                    'prestamosActivos' => intval($stats['prestamos_activos']),
                    'pagosHoy' => floatval($stats['pagos_hoy'])
                ]
            ]);
            break;
            
        case 'prestamos_dia':
            $sql = "SELECT p.*, c.nombre as cliente_nombre 
                   FROM prestamos p 
                   JOIN clientes c ON p.cliente_id = c.id 
                   WHERE DATE(p.fecha_inicio) = CURDATE()";
            
            $result = $conn->query($sql);
            $prestamos = [];
            
            while ($row = $result->fetch_assoc()) {
                $prestamos[] = [
                    'cliente' => $row['cliente_nombre'],
                    'monto' => floatval($row['monto']),
                    'interes' => floatval($row['interes']),
                    'estado' => $row['estado']
                ];
            }
            
            echo json_encode(['success' => true, 'prestamos' => $prestamos]);
            break;
            
        case 'prestamos_vencidos':
            $sql = "SELECT p.*, c.nombre as cliente_nombre,
                   DATEDIFF(CURDATE(), p.fecha_inicio) as dias_vencido
                   FROM prestamos p 
                   JOIN clientes c ON p.cliente_id = c.id 
                   WHERE p.estado = 'Activo' 
                   AND DATE_ADD(p.fecha_inicio, INTERVAL p.plazo DAY) < CURDATE()";
            
            $result = $conn->query($sql);
            $prestamos = [];
            
            while ($row = $result->fetch_assoc()) {
                $prestamos[] = [
                    'cliente' => $row['cliente_nombre'],
                    'monto' => floatval($row['monto']),
                    'diasVencido' => intval($row['dias_vencido']),
                    'estado' => $row['estado']
                ];
            }
            
            echo json_encode(['success' => true, 'prestamos' => $prestamos]);
            break;
            
        case 'pagos_atrasados':
            $sql = "SELECT p.id as prestamo_id, c.nombre as cliente_nombre,
                   pg.numero_cuota, pg.fecha,
                   DATEDIFF(CURDATE(), pg.fecha) as dias_atraso
                   FROM prestamos p 
                   JOIN clientes c ON p.cliente_id = c.id
                   JOIN pagos pg ON p.id = pg.prestamo_id
                   WHERE p.estado = 'Activo'
                   AND pg.fecha < CURDATE()
                   ORDER BY pg.fecha DESC";
            
            $result = $conn->query($sql);
            $pagos = [];
            
            while ($row = $result->fetch_assoc()) {
                $pagos[] = [
                    'cliente' => $row['cliente_nombre'],
                    'prestamo' => $row['prestamo_id'],
                    'numeroCuota' => $row['numero_cuota'],
                    'diasAtraso' => intval($row['dias_atraso'])
                ];
            }
            
            echo json_encode(['success' => true, 'pagos' => $pagos]);
            break;
    }
}
?>