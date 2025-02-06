<?php
// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_prestamos');

// Crear conexión
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Crear base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) !== TRUE) {
    die("Error creando base de datos: " . $conn->error);
}

// Seleccionar la base de datos
$conn->select_db(DB_NAME);

// Crear tablas necesarias
$tables = [
    "CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        tipo ENUM('admin', 'consulta') NOT NULL,
        permisos JSON
    )",
    
    "CREATE TABLE IF NOT EXISTS prestamistas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        rnc VARCHAR(20) NOT NULL,
        direccion TEXT,
        telefono VARCHAR(20),
        email VARCHAR(100),
        web VARCHAR(100),
        estado ENUM('activo', 'inactivo') DEFAULT 'activo'
    )",
    
    "CREATE TABLE IF NOT EXISTS clientes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nombre VARCHAR(100) NOT NULL,
        dni VARCHAR(20) NOT NULL,
        telefono VARCHAR(20),
        direccion TEXT,
        fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )",
    
    "CREATE TABLE IF NOT EXISTS prestamos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cliente_id INT NOT NULL,
        prestamista_id INT NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        interes DECIMAL(5,2) NOT NULL,
        frecuencia ENUM('diario','semanal','quincenal','mensual','anual') NOT NULL,
        plazo INT NOT NULL,
        cuota DECIMAL(10,2) NOT NULL,
        estado ENUM('Activo','Pagado','Vencido') DEFAULT 'Activo',
        fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cliente_id) REFERENCES clientes(id),
        FOREIGN KEY (prestamista_id) REFERENCES prestamistas(id)
    )",
    
    "CREATE TABLE IF NOT EXISTS pagos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        prestamo_id INT NOT NULL,
        monto DECIMAL(10,2) NOT NULL,
        tipo ENUM('regular','mora','pago total') NOT NULL,
        fecha DATE NOT NULL,
        numero_cuota INT,
        FOREIGN KEY (prestamo_id) REFERENCES prestamos(id)
    )"
];

foreach ($tables as $sql) {
    if ($conn->query($sql) !== TRUE) {
        die("Error creando tabla: " . $conn->error);
    }
}

// Insertar usuario admin por defecto si no existe
$adminPass = password_hash('admin123', PASSWORD_DEFAULT);
$permisos = json_encode([
    'clientes' => true,
    'prestamos' => true,
    'pagos' => true,
    'usuarios' => true,
    'reportes' => true,
    'prestamista' => true
]);

$sql = "INSERT IGNORE INTO usuarios (username, password, tipo, permisos) 
        VALUES ('admin', '$adminPass', 'admin', '$permisos')";
$conn->query($sql);

?>