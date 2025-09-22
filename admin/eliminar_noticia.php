<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/conexion.php';

$id = $_GET['id'] ?? 0;
$id = intval($id);

if ($id > 0) {
    // 1. Eliminar imágenes asociadas
    $stmtImg = $conexion->prepare("SELECT ruta_imagen FROM noticia_imagenes WHERE noticia_id = ?");
    $stmtImg->bind_param("i", $id);
    $stmtImg->execute();
    $resultadoImg = $stmtImg->get_result();

    while ($img = $resultadoImg->fetch_assoc()) {
        $ruta = '../' . $img['ruta_imagen'];
        if (file_exists($ruta)) {
            unlink($ruta); // Eliminar archivo físico
        }
    }

    // 2. Eliminar registros de imágenes
    $stmtDelImg = $conexion->prepare("DELETE FROM noticia_imagenes WHERE noticia_id = ?");
    $stmtDelImg->bind_param("i", $id);
    $stmtDelImg->execute();

    // 3. Eliminar PDF si existe
    $stmtPDF = $conexion->prepare("SELECT link_archivo FROM noticias WHERE id = ?");
    $stmtPDF->bind_param("i", $id);
    $stmtPDF->execute();
    $resultadoPDF = $stmtPDF->get_result();
    $pdf = $resultadoPDF->fetch_assoc();

    if (!empty($pdf['link_archivo'])) {
        $rutaPDF = '../' . $pdf['link_archivo'];
        if (file_exists($rutaPDF)) {
            unlink($rutaPDF);
        }
    }

    // 4. Eliminar la noticia
    $stmtDel = $conexion->prepare("DELETE FROM noticias WHERE id = ?");
    $stmtDel->bind_param("i", $id);
    $stmtDel->execute();
}

// Redirigir de vuelta a la gestión
header("Location: gestionar_noticias.php");
exit();
?>
