<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Crear Noticia - CLEF</title>
    <link rel="stylesheet" href="../css/admin.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <div class="container py-5">
        <!-- Botón retroceso -->
        <div class="mb-4">
            <a href="gestionar_noticias.php" class="btn btn-outline-secondary">⬅ Atras</a>
        </div>

        <!-- Tarjeta del formulario -->
        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">➕ Crear Nueva Noticia</h4>
            </div>

            <div class="card-body">
                <form action="procesar_crear_noticia.php" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" name="titulo" id="titulo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="resumen" class="form-label">Resumen (máx. 255 caracteres)</label>
                        <textarea name="resumen" id="resumen" rows="2" class="form-control" maxlength="255" required oninput="actualizarContadorResumen()"></textarea>
                        <small id="contadorResumen" class="form-text text-muted">0 / 255 caracteres</small>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción completa</label>
                        <textarea name="descripcion" id="descripcion" rows="8" class="form-control"></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo</label>
                        <select name="tipo" id="tipo" class="form-select" required>
                            <option value="">Seleccione un tipo</option>
                            <option value="articulos">Artículos</option>
                            <option value="entrevistas">Entrevistas</option>
                            <option value="blog">Blog personal</option>
                            <option value="pastillas">Pastillas informativas</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="fecha" class="form-label">Fecha</label>
                        <input type="date" name="fecha" id="fecha" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Archivo o enlace externo</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_enlace" id="opcion_pdf" value="pdf" checked>
                            <label class="form-check-label" for="opcion_pdf">Subir archivo PDF</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="tipo_enlace" id="opcion_link" value="link">
                            <label class="form-check-label" for="opcion_link">Ingresar un enlace externo (Drive, Dropbox, etc.)</label>
                        </div>
                    </div>

                    <div class="mb-3" id="grupo_pdf">
                        <label for="archivo_pdf" class="form-label">Seleccionar archivo PDF</label>
                        <input type="file" name="archivo_pdf" id="archivo_pdf" class="form-control" accept="application/pdf">
                    </div>

                    <div class="mb-3 d-none" id="grupo_link">
                        <label for="enlace_externo" class="form-label">Enlace externo</label>
                        <input type="url" name="enlace_externo" id="enlace_externo" class="form-control" placeholder="https://drive.google.com/..." pattern="https?://.*">
                    </div>

                    <div class="mb-3">
                        <label for="imagenes" class="form-label">Imágenes de la noticia</label>
                        <input type="file" name="imagenes[]" id="imagenes" class="form-control" accept="image/*" multiple required>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">💾 Guardar Noticia</button>
                        <a href="gestionar_noticias.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Script para mostrar u ocultar PDF/enlace -->
    <script src="https://cdn.ckeditor.com/ckeditor5/41.2.1/classic/ckeditor.js"></script>
    <script>
        ClassicEditor
            .create(document.querySelector('#descripcion'))
            .catch(error => {
                console.error(error);
            });
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const opcionPDF = document.getElementById('opcion_pdf');
            const opcionLink = document.getElementById('opcion_link');
            const grupoPDF = document.getElementById('grupo_pdf');
            const grupoLink = document.getElementById('grupo_link');

            function toggleCampos() {
                if (opcionPDF.checked) {
                    grupoPDF.classList.remove('d-none');
                    grupoLink.classList.add('d-none');
                } else {
                    grupoPDF.classList.add('d-none');
                    grupoLink.classList.remove('d-none');
                }
            }

            opcionPDF.addEventListener('change', toggleCampos);
            opcionLink.addEventListener('change', toggleCampos);
        });
    </script>
    <script>
        function actualizarContadorResumen() {
            const resumen = document.getElementById("resumen");
            const contador = document.getElementById("contadorResumen");
            const longitud = resumen.value.length;

            contador.textContent = `${longitud} / 255 caracteres`;

            if (longitud > 255) {
                contador.classList.remove("text-muted");
                contador.classList.add("text-danger");
            } else {
                contador.classList.add("text-muted");
                contador.classList.remove("text-danger");
            }
        }

        document.addEventListener("DOMContentLoaded", actualizarContadorResumen);
    </script>
</body>

</html>