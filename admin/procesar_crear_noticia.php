<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/conexion.php';

// SOLO UNA VEZ (fuera de la función):
require_once __DIR__ . '/../vendor/autoload.php';
use Google\Auth\Credentials\ServiceAccountCredentials;

// Procesar los campos de la noticia como ya lo tienes
$titulo = trim($_POST['titulo']);
$resumen = trim($_POST['resumen']);
$descripcion = trim($_POST['descripcion']);
$tipo = $_POST['tipo'];
$fecha = $_POST['fecha'];
$link_archivo = null;

// 1. MANEJAR LINK O PDF
if ($_POST['tipo_enlace'] === 'link') {
    $link_archivo = trim($_POST['enlace_externo']);
} elseif ($_FILES['archivo_pdf']['error'] === UPLOAD_ERR_OK) {
    $nombrePDF = time() . '_' . basename($_FILES['archivo_pdf']['name']);
    $rutaPDF = '../uploads/pdf/' . $nombrePDF;

    if (!is_dir('../uploads/pdf')) {
        mkdir('../uploads/pdf', 0775, true);
    }

    if (move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $rutaPDF)) {
        $link_archivo = str_replace('../', '', $rutaPDF);
    }
}

// 2. INSERTAR NOTICIA
$stmt = $conexion->prepare("INSERT INTO noticias (titulo, resumen, descripcion, tipo, fecha, link_archivo) VALUES (?, ?, ?, ?, ?, ?)");
$stmt->bind_param("ssssss", $titulo, $resumen, $descripcion, $tipo, $fecha, $link_archivo);
$stmt->execute();
$noticia_id = $stmt->insert_id;

// 3. SUBIR IMÁGENES
if (!empty($_FILES['imagenes']['name'][0])) {
    if (!is_dir('../uploads/imagenes')) {
        mkdir('../uploads/imagenes', 0775, true);
    }
    foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
        if ($_FILES['imagenes']['error'][$index] === UPLOAD_ERR_OK) {
            $nombreImagen = time() . '_' . basename($_FILES['imagenes']['name'][$index]);
            $rutaImagen = '../uploads/imagenes/' . $nombreImagen;
            if (move_uploaded_file($tmpName, $rutaImagen)) {
                $rutaRelativa = str_replace('../', '', $rutaImagen);
                $stmtImg = $conexion->prepare("INSERT INTO noticia_imagenes (noticia_id, ruta_imagen) VALUES (?, ?)");
                $stmtImg->bind_param("is", $noticia_id, $rutaRelativa);
                $stmtImg->execute();
            }
        }
    }
}

// 4. ENVIAR NOTIFICACIÓN MODERNA
function enviarNotificacionFirebaseV1($titulo, $descripcion, $noticia_id) {
    $serviceAccountPath = __DIR__ . '/../keys/firebase_service_account.json';
    $projectId = 'camarafortalezaapp'; // Asegúrate que es EXACTAMENTE el Project ID de Firebase

    $credentials = new ServiceAccountCredentials(
        'https://www.googleapis.com/auth/firebase.messaging',
        $serviceAccountPath
    );
    $token = $credentials->fetchAuthToken()['access_token'];

    $url = "https://fcm.googleapis.com/v1/projects/$projectId/messages:send";
    $message = [
        'message' => [
            'topic' => 'noticias',
            'notification' => [
                'title' => $titulo,
                'body' => $descripcion
            ],
            'data' => [
                'noticia_id' => (string)$noticia_id
            ]
        ]
    ];

    $headers = [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json; UTF-8'
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($message));
    $result = curl_exec($ch);
    if ($result === FALSE) {
        error_log('Error enviando notificación FCM V1: ' . curl_error($ch));
    }
    curl_close($ch);
}

// 5. ENVIAR NOTIFICACIÓN AL TOPIC
enviarNotificacionFirebaseV1($titulo, $descripcion, $noticia_id);

// 6. REDIRECCIONAR
header("Location: gestionar_noticias.php");
exit();
