<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/conexion.php';

// Sanitizar datos
$id = intval($_POST['id']);
$titulo = trim($_POST['titulo']);
$descripcion = trim($_POST['descripcion']);
$tipo = $_POST['tipo'];
$fecha = $_POST['fecha'];
$link_archivo = null;

// Obtener link_archivo actual
$stmt = $conexion->prepare("SELECT link_archivo FROM noticias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$actual = $res->fetch_assoc();
$link_archivo = $actual['link_archivo'];

// Reemplazar PDF o enlace si se envía uno nuevo
if ($_POST['tipo_enlace'] === 'link' && !empty($_POST['enlace_externo'])) {
    $link_archivo = trim($_POST['enlace_externo']);
} elseif ($_POST['tipo_enlace'] === 'pdf' && $_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
    $nombrePDF = time() . '_' . basename($_FILES['archivo_pdf']['name']);
    $rutaPDF = '../uploads/pdf/' . $nombrePDF;

    if (!is_dir('../uploads/pdf')) {
        mkdir('../uploads/pdf', 0775, true);
    }

    if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $rutaPDF)) {
        $link_archivo = str_replace('../', '', $rutaPDF);
    }
}

// Actualizar noticia
$stmt = $conexion->prepare("UPDATE noticias SET titulo=?, descripcion=?, tipo=?, fecha=?, link_archivo=? WHERE id=?");
$stmt->bind_param("sssssi", $titulo, $descripcion, $tipo, $fecha, $link_archivo, $id);
$stmt->execute();

// Subir nuevas imágenes si se adjuntan
if (!empty($_FILES['imagenes']['name'][0])) {
    if (!is_dir('../uploads/imagenes')) {
        mkdir('../uploads/imagenes', 0775, true);
    }

    foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['imagenes']['error'][$index] === UPLOAD_ERR_OK) {
            $nombreImg = time() . '_' . basename($_FILES['imagenes']['name'][$index]);
            $rutaImagen = '../uploads/imagenes/' . $nombreImg;

            if (move_uploaded_file($tmpName, $rutaImagen)) {
                $rutaRelativa = str_replace('../', '', $rutaImagen);
                $stmtImg = $conexion->prepare("INSERT INTO noticia_imagenes (noticia_id, ruta_imagen) VALUES (?, ?)");
                $stmtImg->bind_param("is", $id, $rutaRelativa);
                $stmtImg->execute();
            }
        }
    }
}

header("Location: gestionar_noticias.php");
exit();
?>
