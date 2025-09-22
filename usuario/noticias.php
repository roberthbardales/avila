<?php
require_once '../includes/conexion.php';

$sql = "SELECT n.id, n.titulo, n.descripcion, n.tipo, n.fecha, n.likes, n.link_archivo,
        (
          SELECT ruta_imagen 
          FROM noticia_imagenes 
          WHERE noticia_id = n.id 
          ORDER BY id ASC LIMIT 1
        ) AS imagen_principal
        FROM noticias n 
        ORDER BY fecha DESC";

$res = $conexion->query($sql);
$noticias = [];

while ($row = $res->fetch_assoc()) {
    // RUTA DE IMAGEN (si existe y no está vacía)
    if (!empty($row['imagen_principal'])) {
        // Si la ruta NO empieza con '/', agrégala
        $ruta = ltrim($row['imagen_principal'], '/');
        $row['imagen_principal'] = '/clef/' . $ruta;
    } else {
        // Imagen por defecto
        $row['imagen_principal'] = '/clef/imagenes/no-image.jpg';
    }

    // RESUMEN
    $row['resumen'] = mb_strlen($row['descripcion']) > 120
        ? mb_substr(strip_tags($row['descripcion']), 0, 120) . '...'
        : strip_tags($row['descripcion']);

    // GARANTIZAR LIKES NUMÉRICO
    $row['likes'] = isset($row['likes']) ? (int) $row['likes'] : 0;

    // FECHA en formato Y-m-d, opcional
    if (!empty($row['fecha'])) {
        $row['fecha'] = date('Y-m-d', strtotime($row['fecha']));
    }

    $noticias[] = $row;
}

header('Content-Type: application/json');
echo json_encode($noticias);
