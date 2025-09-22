<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

require_once '../includes/conexion.php';

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: gestionar_noticias.php");
    exit();
}

$id = intval($_GET['id']);

// Obtener datos de la noticia
$stmt = $conexion->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    header("Location: gestionar_noticias.php");
    exit();
}

$noticia = $resultado->fetch_assoc();

// Obtener imágenes
$stmtImg = $conexion->prepare("SELECT id, ruta_imagen FROM noticia_imagenes WHERE noticia_id = ?");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$imagenes = $stmtImg->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Noticia - CLEF</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
    <a href="gestionar_noticias.php" class="btn btn-outline-secondary mb-4">⬅ Volver a gestión</a>

    <div class="card shadow">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0">✏️ Editar Noticia</h4>
        </div>

        <div class="card-body">
            <form action="procesar_editar_noticia.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= $noticia['id'] ?>">

                <div class="mb-3">
                    <label class="form-label">Título</label>
                    <input type="text" name="titulo" value="<?= htmlspecialchars($noticia['titulo']) ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" rows="5" class="form-control" required><?= htmlspecialchars($noticia['descripcion']) ?></textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tipo</label>
                    <select name="tipo" class="form-select" required>
                        <?php
                        $tipos = ['penal', 'sanciones', 'cts', 'lo-ultimo'];
                        foreach ($tipos as $tipo) {
                            $selected = ($noticia['tipo'] === $tipo) ? 'selected' : '';
                            echo "<option value=\"$tipo\" $selected>" . ucfirst($tipo) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Fecha</label>
                    <input type="date" name="fecha" value="<?= $noticia['fecha'] ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Archivo PDF actual o enlace</label>
                    <?php if (!empty($noticia['link_archivo'])): ?>
                        <p><a href="../<?= $noticia['link_archivo'] ?>" target="_blank">📎 Ver archivo actual</a></p>
                    <?php endif; ?>
                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_enlace" id="opcion_pdf" value="pdf">
                        <label class="form-check-label" for="opcion_pdf">Subir nuevo PDF</label>
                    </div>
                    <input type="file" name="archivo_pdf" class="form-control mb-2" accept="application/pdf">

                    <div class="form-check">
                        <input class="form-check-input" type="radio" name="tipo_enlace" id="opcion_link" value="link">
                        <label class="form-check-label" for="opcion_link">Reemplazar con un nuevo enlace externo</label>
                    </div>
                    <input type="url" name="enlace_externo" class="form-control" placeholder="https://...">
                </div>

                <div class="mb-3">
                    <label class="form-label">Imágenes actuales</label><br>
                    <?php while ($img = $imagenes->fetch_assoc()): ?>
                        <div class="d-inline-block position-relative me-2 mb-2">
                            <img src="../<?= $img['ruta_imagen'] ?>" width="100" height="100" class="border rounded">
                            <a href="eliminar_imagen.php?id=<?= $img['id'] ?>&noticia=<?= $noticia['id'] ?>" class="btn btn-sm btn-danger position-absolute top-0 end-0" onclick="return confirm('¿Eliminar esta imagen?')">×</a>
                        </div>
                    <?php endwhile; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label">Agregar nuevas imágenes</label>
                    <input type="file" name="imagenes[]" class="form-control" accept="image/*" multiple>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-primary">💾 Guardar Cambios</button>
                    <a href="gestionar_noticias.php" class="btn btn-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>
