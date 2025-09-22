<?php
require_once '../includes/conexion.php'; // ajusta la ruta si es necesario

header('Content-Type: application/json; charset=utf-8');

// 1. Obtener el ID de la noticia por GET
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "ID no válido"]);
    exit;
}

// 2. Buscar la noticia
$stmt = $conexion->prepare("SELECT id, titulo, descripcion, tipo, fecha, likes, link_archivo FROM noticias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$noticia = $res->fetch_assoc();

if (!$noticia) {
    echo json_encode(["success" => false, "message" => "No encontrada"]);
    exit;
}

// 3. Buscar imagen principal
$stmtImg = $conexion->prepare("SELECT ruta_imagen FROM noticia_imagenes WHERE noticia_id = ? ORDER BY id ASC LIMIT 1");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$resImg = $stmtImg->get_result();
$imgRow = $resImg->fetch_assoc();
$noticia['imagen_principal'] = $imgRow && !empty($imgRow['ruta_imagen'])
    ? '/clef/' . ltrim($imgRow['ruta_imagen'], '/')
    : '/clef/imagenes/no-image.jpg';

// 4. Limpiar/castear datos
$noticia['likes'] = isset($noticia['likes']) ? (int)$noticia['likes'] : 0;
$noticia['fecha'] = !empty($noticia['fecha']) ? date('Y-m-d', strtotime($noticia['fecha'])) : '';

echo json_encode([
    "success" => true,
    "noticia" => $noticia
]);
