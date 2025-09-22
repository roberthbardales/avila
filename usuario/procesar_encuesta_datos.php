<?php
session_start();
require_once '../includes/conexion.php';

// Verificar sesión activa
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

$clienteId = $_SESSION['cliente_id'];

// Recibir datos del formulario (sin DNI)
$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$provincia = trim($_POST['provincia'] ?? '');

// Validaciones
$errores = [];

if (empty($nombre)) {
    $errores[] = "El nombre es obligatorio.";
}

if (empty($correo)) {
    $errores[] = "El correo es obligatorio.";
} elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $errores[] = "El correo no tiene un formato válido.";
}

if (strlen($telefono) !== 9 || !ctype_digit($telefono)) {
    $errores[] = "El teléfono debe tener exactamente 9 dígitos.";
}

if (empty($provincia)) {
    $errores[] = "La provincia es obligatoria.";
}

// Si hay errores, mostrar y detener
if (!empty($errores)) {
    foreach ($errores as $error) {
        echo "<p style='color: red;'>⚠️ $error</p>";
    }
    echo "<p><a href='encuesta_datos.php'>⬅️ Volver a completar datos</a></p>";
    exit();
}

// Actualizar datos si todo es correcto
$stmt = $conexion->prepare("UPDATE clientes SET nombre = ?, correo = ?, telefono = ?, provincia = ? WHERE id = ?");
$stmt->bind_param("ssssi", $nombre, $correo, $telefono, $provincia, $clienteId);
$stmt->execute();

// Redirigir al dashboard
header("Location: dashboard.php");
exit();
?>
