<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'registrar':
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $rnc = $conn->real_escape_string($_POST['rnc']);
            $direccion = $conn->real_escape_string($_POST['direccion']);
            $telefono = $conn->real_escape_string($_POST['telefono']);
            $email = $conn->real_escape_string($_POST['email']);
            $web = $conn->real_escape_string($_POST['web']);
            $estado = $conn->real_escape_string($_POST['estado']);
            
            $sql = "INSERT INTO prestamistas (nombre, rnc, direccion, telefono, email, web, estado) 
                    VALUES ('$nombre', '$rnc', '$direccion', '$telefono', '$email', '$web', '$estado')";
            
            if ($conn->query($sql)) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Prestamista registrado correctamente',
                    'id' => $conn->insert_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el prestamista']);
            }
            break;

        case 'obtener':
            $sql = "SELECT * FROM prestamistas ORDER BY nombre";
            $result = $conn->query($sql);
            $prestamistas = [];
            
            while ($row = $result->fetch_assoc()) {
                $prestamistas[] = $row;
            }
            
            echo json_encode(['success' => true, 'prestamistas' => $prestamistas]);
            break;

        case 'actualizar':
            $id = $conn->real_escape_string($_POST['id']);
            $nombre = $conn->real_escape_string($_POST['nombre']);
            $rnc = $conn->real_escape_string($_POST['rnc']);
            $direccion = $conn->real_escape_string($_POST['direccion']);
            $telefono = $conn->real_escape_string($_POST['telefono']);
            $email = $conn->real_escape_string($_POST['email']);
            $web = $conn->real_escape_string($_POST['web']);
            $estado = $conn->real_escape_string($_POST['estado']);
            
            $sql = "UPDATE prestamistas 
                    SET nombre = '$nombre', rnc = '$rnc', direccion = '$direccion',
                        telefono = '$telefono', email = '$email', web = '$web', 
                        estado = '$estado'
                    WHERE id = '$id'";
            
            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Prestamista actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar el prestamista']);
            }
            break;

        case 'eliminar':
            $id = $conn->real_escape_string($_POST['id']);
            
            // Verificar si tiene préstamos asociados
            $sql = "SELECT COUNT(*) as total FROM prestamos WHERE prestamista_id = '$id'";
            $result = $conn->query($sql);
            $count = $result->fetch_assoc()['total'];
            
            if ($count > 0) {
                echo json_encode([
                    'success' => false, 
                    'message' => 'No se puede eliminar el prestamista porque tiene préstamos asociados'
                ]);
                break;
            }
            
            $sql = "DELETE FROM prestamistas WHERE id = '$id'";
            
            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Prestamista eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el prestamista']);
            }
            break;

        case 'verificar_rnc':
            $rnc = $conn->real_escape_string($_POST['rnc']);
            $id = isset($_POST['id']) ? $conn->real_escape_string($_POST['id']) : null;
            
            $sql = "SELECT COUNT(*) as total FROM prestamistas WHERE rnc = '$rnc'";
            if ($id) {
                $sql .= " AND id != '$id'";
            }
            
            $result = $conn->query($sql);
            $existe = $result->fetch_assoc()['total'] > 0;
            
            echo json_encode(['success' => true, 'existe' => $existe]);
            break;
    }
}
?>