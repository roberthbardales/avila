<?php
session_start();
include '../includes/conexion.php';

$dni = $_POST['dni'];
$password = $_POST['password'];

$sql = "SELECT * FROM clientes WHERE dni = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $dni);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $cliente = $resultado->fetch_assoc();

    if (password_verify($password, $cliente['password'])) {
        $_SESSION['cliente_id'] = $cliente['id'];
        $_SESSION['cliente_nombre'] = $cliente['nombre'] ?? 'Usuario';

        // Verificar campos requeridos
        $faltanDatos = false;
        $camposRequeridos = ['nombre', 'correo', 'telefono', 'provincia'];
        foreach ($camposRequeridos as $campo) {
            if (empty($cliente[$campo])) {
                $faltanDatos = true;
                break;
            }
        }

        if ($faltanDatos) {
            header("Location: encuesta_datos.php");
        } else {
            header("Location: dashboard.php");
        }
        exit();
    } else {
        // Contraseña incorrecta
        header("Location: login.php?error=clave");
        exit();
    }
} else {
    // Usuario no encontrado
    header("Location: login.php?error=usuario");
    exit();
}
