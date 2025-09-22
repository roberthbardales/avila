<?php
session_start();

// Verificar si el usuario tiene sesión como administrador
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Conexión a la base de datos
require_once '../includes/conexion.php';

$toastMessage = ''; // Inicializar mensaje para notificación

// Verificar si se ha enviado el formulario por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y sanitizar datos del formulario
    $nombre        = trim($_POST['nombre']);
    $fecha         = $_POST['fecha_sorteo'];
    $descripcion   = trim($_POST['descripcion']);
    $activo        = $_POST['activo'] === 'si' ? 'si' : 'no';
    $destinatario  = isset($_POST['destinatario']) ? intval($_POST['destinatario']) : 1;

    // Asegurar que la carpeta de imágenes exista
    $carpetaImagenes = '../img_premios/';
    if (!is_dir($carpetaImagenes)) {
        mkdir($carpetaImagenes, 0755, true);
    }

    // Insertar el sorteo sin imágenes aún
    $sql = "INSERT INTO sorteos (nombre, fecha_sorteo, descripcion, activo, destinatario)
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $fecha, $descripcion, $activo, $destinatario);
    $stmt->execute();
    $sorteoId = $stmt->insert_id;

    // Procesar múltiples imágenes
    if (!empty($_FILES['imagenes']['name'][0])) {
        foreach ($_FILES['imagenes']['tmp_name'] as $index => $tmpName) {
            if ($_FILES['imagenes']['error'][$index] === UPLOAD_ERR_OK) {
                $extension = pathinfo($_FILES['imagenes']['name'][$index], PATHINFO_EXTENSION);
                $nombreFinal = 'premio_' . uniqid() . '.' . $extension;
                $rutaDestino = $carpetaImagenes . $nombreFinal;

                if (move_uploaded_file($tmpName, $rutaDestino)) {
                    $rutaGuardada = 'img_premios/' . $nombreFinal;

                    // Insertar en imagenes_sorteo
                    $stmtImg = $conexion->prepare("INSERT INTO imagenes_sorteo (sorteo_id, imagen_url) VALUES (?, ?)");
                    $stmtImg->bind_param("is", $sorteoId, $rutaGuardada);
                    $stmtImg->execute();
                }
            }
        }
        $toastMessage = '✅ Sorteo creado y todas las imágenes subidas correctamente.';
    } else {
        $toastMessage = '✅ Sorteo creado, pero no se subieron imágenes.';
    }

    // Redirigir con notificación visual
    echo "<script>
        localStorage.setItem('toast', '$toastMessage');
        setTimeout(() => { window.location.href = 'admin_dashboard.php'; }, 100);
    </script>";
    exit();
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Sorteo - Admin CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="form-container">
        <h2>🎁 Crear nuevo sorteo</h2>

        <form method="POST" enctype="multipart/form-data">
            <label for="nombre">Nombre del sorteo:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="fecha_sorteo">Fecha y hora del sorteo:</label>
            <input type="datetime-local" id="fecha_sorteo" name="fecha_sorteo" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion" rows="4" required></textarea>

            <label for="activo">¿Activo?</label>
            <select id="activo" name="activo">
                <option value="si">Sí</option>
                <option value="no">No</option>
            </select>

            <label for="destinatario">¿Quiénes pueden participar?</label>
            <select id="destinatario" name="destinatario">
                <option value="1">🌐 Todos</option>
                <option value="2">⭐ Solo afiliados</option>
                <option value="0">🧍‍♂️ Solo no afiliados</option>
            </select>

            <label for="imagenes">Imágenes del premio:</label>
            <input type="file" id="imagenes" name="imagenes[]" accept="image/*" multiple>

            <input type="submit" value="Crear sorteo">
        </form>

        <p style="text-align: center; margin-top: 20px;">
            <a href="admin_dashboard.php" class="button" style="background-color: #999;">
                ⬅️ Volver al panel de administración
            </a>
        </p>
    </div>

    <!-- Toast visual de notificación -->
    <script>
        window.addEventListener("DOMContentLoaded", () => {
            const mensaje = localStorage.getItem("toast");
            if (mensaje) {
                const toast = document.createElement("div");
                toast.textContent = mensaje;
                toast.style.position = "fixed";
                toast.style.bottom = "30px";
                toast.style.right = "30px";
                toast.style.background = "#333";
                toast.style.color = "#fff";
                toast.style.padding = "12px 20px";
                toast.style.borderRadius = "8px";
                toast.style.boxShadow = "0 4px 12px rgba(0,0,0,0.3)";
                toast.style.zIndex = "9999";
                toast.style.fontWeight = "bold";
                document.body.appendChild(toast);
                setTimeout(() => toast.remove(), 4000);
                localStorage.removeItem("toast");
            }
        });
    </script>
</body>

</html>