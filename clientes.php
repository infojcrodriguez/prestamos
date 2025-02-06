<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'registrar':
            // Get form data with proper validation
            $nombre = isset($_POST['nombreCliente']) ? $conn->real_escape_string($_POST['nombreCliente']) : '';
            $dni = isset($_POST['dniCliente']) ? $conn->real_escape_string($_POST['dniCliente']) : '';
            $telefono = isset($_POST['telefonoCliente']) ? $conn->real_escape_string($_POST['telefonoCliente']) : '';
            $direccion = isset($_POST['direccionCliente']) ? $conn->real_escape_string($_POST['direccionCliente']) : '';
            
            // Validate required fields
            if (empty($nombre) || empty($dni)) {
                echo json_encode(['success' => false, 'message' => 'Nombre y DNI son campos requeridos']);
                exit;
            }
            
            $sql = "INSERT INTO clientes (nombre, dni, telefono, direccion) 
                    VALUES ('$nombre', '$dni', '$telefono', '$direccion')";
            
            if ($conn->query($sql)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cliente registrado correctamente',
                    'cliente' => [
                        'id' => $conn->insert_id,
                        'nombre' => $nombre,
                        'dni' => $dni,
                        'telefono' => $telefono,
                        'direccion' => $direccion
                    ]
                ]);
            } else {
                echo json_encode([
                    'success' => false, 
                    'message' => 'Error al registrar el cliente: ' . $conn->error
                ]);
            }
            break;
            
        case 'obtener':
            $sql = "SELECT * FROM clientes ORDER BY fecha_registro DESC";
            $result = $conn->query($sql);
            $clientes = [];
            
            while ($row = $result->fetch_assoc()) {
                $clientes[] = $row;
            }
            
            echo json_encode(['success' => true, 'clientes' => $clientes]);
            break;
            
        case 'actualizar':
            $id = $conn->real_escape_string($_POST['id']);
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $dni = $conn->real_escape_string($_POST['dni']);
            $telefono = $conn->real_escape_string($_POST['telefono']);
            $direccion = $conn->real_escape_string($_POST['direccion']);
            
            $sql = "UPDATE clientes 
                    SET nombre = '$nombre', dni = '$dni', 
                        telefono = '$telefono', direccion = '$direccion' 
                    WHERE id = '$id'";
            
            if ($conn->query($sql)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Cliente actualizado correctamente',
                    'cliente' => [
                        'id' => $id,
                        'nombre' => $nombre,
                        'dni' => $dni,
                        'telefono' => $telefono,
                        'direccion' => $direccion
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el cliente']);
            }
            break;
            
        case 'eliminar':
            $id = $conn->real_escape_string($_POST['id']);
            
            // Verificar si el cliente tiene préstamos
            $sqlCheck = "SELECT COUNT(*) as count FROM prestamos WHERE cliente_id = '$id'";
            $result = $conn->query($sqlCheck);
            $count = $result->fetch_assoc()['count'];
            
            if ($count > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar el cliente porque tiene préstamos asociados']);
                break;
            }
            
            $sql = "DELETE FROM clientes WHERE id = '$id'";
            
            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el cliente']);
            }
            break;
    }
}
?>