<?php
require_once 'config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'];
    
    switch($action) {
        case 'registrar':
            $username = $conn->real_escape_string($_POST['username']);
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $tipo = $conn->real_escape_string($_POST['tipo']);
            $permisos = $conn->real_escape_string($_POST['permisos']);
            
            $sql = "INSERT INTO usuarios (username, password, tipo, permisos) 
                    VALUES ('$username', '$password', '$tipo', '$permisos')";
            
            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Usuario registrado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario']);
            }
            break;
            
        case 'obtener':
            $sql = "SELECT id, username, tipo, permisos FROM usuarios";
            $result = $conn->query($sql);
            $usuarios = [];
            
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $usuarios[] = [
                        'id' => $row['id'],
                        'username' => $row['username'],
                        'tipo' => $row['tipo'],
                        'permisos' => $row['permisos']
                    ];
                }
                echo json_encode(['success' => true, 'usuarios' => $usuarios]);
            } else {
                echo json_encode(['success' => true, 'usuarios' => []]);
            }
            break;
            
        case 'eliminar':
            $id = $conn->real_escape_string($_POST['id']);
            
            // Verificar que no sea el usuario admin
            $sql = "SELECT username FROM usuarios WHERE id = '$id'";
            $result = $conn->query($sql);
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if ($user['username'] === 'admin') {
                    echo json_encode(['success' => false, 'message' => 'No se puede eliminar el usuario administrador']);
                    break;
                }
            }
            
            $sql = "DELETE FROM usuarios WHERE id = '$id' AND username != 'admin'";
            
            if ($conn->query($sql)) {
                echo json_encode(['success' => true, 'message' => 'Usuario eliminado correctamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar el usuario']);
            }
            break;
            
        case 'cambiar_password':
            $id = $conn->real_escape_string($_POST['id']);
            $currentPassword = $_POST['currentPassword'];
            $newPassword = password_hash($_POST['newPassword'], PASSWORD_DEFAULT);
            
            $sql = "SELECT password FROM usuarios WHERE id = '$id'";
            $result = $conn->query($sql);
            
            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();
                if (password_verify($currentPassword, $user['password'])) {
                    $sql = "UPDATE usuarios SET password = '$newPassword' WHERE id = '$id'";
                    if ($conn->query($sql)) {
                        echo json_encode(['success' => true, 'message' => 'Contraseña actualizada correctamente']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            }
            break;
    }
}
?>