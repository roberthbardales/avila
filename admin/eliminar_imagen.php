<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/conexion.php';

if (!isset($_GET['id']) || !isset($_GET['noticia'])) {
    header("Location: gestionar_noticias.php");
    exit();
}

$id_imagen = intval($_GET['id']);
$id_noticia = intval($_GET['noticia']);

// Obtener ruta de la imagen
$stmt = $conexion->prepare("SELECT ruta_imagen FROM noticia_imagenes WHERE id = ?");
$stmt->bind_param("i", $id_imagen);
$stmt->execute();
$res = $stmt->get_result();

if ($res->num_rows === 0) {
    header("Location: editar_noticia.php?id=$id_noticia");
    exit();
}

$imagen = $res->fetch_assoc();
$ruta = '../' . $imagen['ruta_imagen'];

// Eliminar archivo del sistema
if (file_exists($ruta)) {
    unlink($ruta);
}

// Eliminar registro de BD
$stmtDel = $conexion->prepare("DELETE FROM noticia_imagenes WHERE id = ?");
$stmtDel->bind_param("i", $id_imagen);
$stmtDel->execute();

// Redirigir de nuevo a edición
header("Location: editar_noticia.php?id=$id_noticia");
exit();
?>
