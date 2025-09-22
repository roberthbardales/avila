<?php
// like_noticia.php
require_once '../includes/conexion.php';

// Recibe el ID de la noticia por POST
$id = $_POST['id'] ?? 0;

// Valida el ID
if (!$id || !is_numeric($id)) {
    echo json_encode(['success' => false, 'message' => 'ID inválido']);
    exit;
}

// Suma un like a la noticia
$stmt = $conexion->prepare("UPDATE noticias SET likes = likes + 1 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Obtiene el nuevo conteo de likes
$stmt = $conexion->prepare("SELECT likes FROM noticias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$likes = 0;
if ($row = $result->fetch_assoc()) {
    $likes = $row['likes'];
}

// Devuelve el resultado en JSON
echo json_encode([
    'success' => true,
    'likes' => $likes
]);
?>
