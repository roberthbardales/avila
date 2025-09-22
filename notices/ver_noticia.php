<?php
require_once '../includes/conexion.php';
$id = $_GET['id'] ?? 0;

// Obtener noticia
$stmt = $conexion->prepare("SELECT * FROM noticias WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$noticia = $stmt->get_result()->fetch_assoc();

if (!$noticia) {
    echo "<h2>Noticia no encontrada</h2>";
    exit();
}

// Imagen principal
$stmtImg = $conexion->prepare("SELECT ruta_imagen FROM noticia_imagenes WHERE noticia_id = ? ORDER BY id ASC LIMIT 1");
$stmtImg->bind_param("i", $id);
$stmtImg->execute();
$resultImg = $stmtImg->get_result();
$img = $resultImg->fetch_assoc();

$imagen = $img && !empty($img['ruta_imagen']) 
    ? '/clef/' . ltrim($img['ruta_imagen'], '/') 
    : '/clef/imagenes/no-image.jpg';


// Anterior y siguiente
$stmtAnt = $conexion->prepare("SELECT id FROM noticias WHERE id < ? ORDER BY id DESC LIMIT 1");
$stmtAnt->bind_param("i", $id);
$stmtAnt->execute();
$anterior = $stmtAnt->get_result()->fetch_assoc()['id'] ?? $id;

$stmtSig = $conexion->prepare("SELECT id FROM noticias WHERE id > ? ORDER BY id ASC LIMIT 1");
$stmtSig->bind_param("i", $id);
$stmtSig->execute();
$siguiente = $stmtSig->get_result()->fetch_assoc()['id'] ?? $id;
?>



<!DOCTYPE html>
<html lang="es">

<!-- Mirrored from www.sharjeelanjum.com/html/lawfirm/demo/blog-list2.html by HTTrack Website Copier/3.x [XR&CO'2010], Tue, 01 Dec 2020 18:16:46 GMT -->

<head>
    <meta charset="UTF-8">
    <title>CÁMARA LEGAL EDUCATIVA FORTALEZA</title>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="description" content="Fortaleza">
    <meta name="keywords" content="one page, html, template, responsive, business">
    <meta name="author" content="sharjeel anjum">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Bootstrap css -->
    <link rel="stylesheet" href="../../css/bootstrap.min.css">

    <!-- Fontawesome css -->
    <link rel="stylesheet" href="../../css/font-awesome.min.css">

    <!-- Main css -->
    <link rel="stylesheet" href="../../css/style.css">
</head>
<style>
    .btn-like-disabled {
        background-color: #bdbdbd !important;
        color: #fff !important;
        border: none;
    }
</style>

<body
    class="subpage"
    data-spy="scroll"
    data-target=".navbar-collapse"
    data-offset="50">
    <!-- Navigation Section -->
    <div
        class="navbar custom-navbar wow fadeInDown"
        data-wow-duration="2s"
        role="navigation"
        id="header">
        <div class="container">
            <!-- NAVBAR HEADER -->
            <div class="navbar-header">
                <button
                    class="navbar-toggle"
                    data-toggle="collapse"
                    data-target=".navbar-collapse">
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                    <span class="icon icon-bar"></span>
                </button>
                <!-- lOGO TEXT HERE -->
                <a href="index.html" class="navbar-brand"><img src="../../images/logo.png" class="whtlogo" alt="" />
                    <img src="../../images/logo-color.png" class="logocolor" alt="" /></a>
            </div>

            <!-- NAVIGATION LINKS -->
            <div class="collapse navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <li class="dropdown active">
                        <a href="/index.html">INICIO<span class="caret"></span></a>
                    </li>
                    <li><a href="/nosotros.html">NOSOTROS</a></li>
                    <li><a href="/programa.html">PROGRAMAS</a></li>
                    <li>
                        <a href="/blog.php" class="dropdown">BLOG<span class="caret"></span></a>
                    </li>
                    <li class="afiliacion.html">
                        <a href="#">AFILIACIÓN<span class="caret"></span></a>
                    </li>
                    <li><a href="/contacto.html">CONTACTO</a></li>
                    <li>
                        <span class="calltxt"><i class="fa fa-phone" aria-hidden="true"></i> (01) 500
                            5582</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- Dentro del HTML -->
    <div class="detalle-noticia-wrapper">
        <div class="btn-navegacion izquierda">
            <a href="ver_noticia.php?id=<?= $anterior ?>" class="btn btn-outline-primary">
                <i class="fa fa-arrow-left"></i> Anterior
            </a>
        </div>

        <div class="contenido-noticia">
            <h2 class="titulo-noticia"><?= htmlspecialchars($noticia['titulo']) ?></h2>
            <div class="detalle-noticia">
                <div class="imagen">
                    <img src="<?= $imagen ?>" alt="<?= htmlspecialchars($noticia['titulo']) ?>">
                    <div class="meta-info">
                        <span class="fecha"><?= date('d/m/Y', strtotime($noticia['fecha'])) ?></span>
                        <button id="like-btn" class="btn btn-danger">
                            ❤️ <span id="like-count"><?= $noticia['likes'] ?? 0 ?></span>
                        </button>
                    </div>
                </div>
                <div class="descripcion-ver-noticia">
                    <p class="texto-descripcion"><?= nl2br(htmlspecialchars($noticia['descripcion'])) ?></p>
                    <?php if (!empty($noticia['link_archivo'])): ?>
                        <a href="../../<?= $noticia['link_archivo'] ?>" download class="btn btn-primary mt-3">Descargar PDF</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="btn-navegacion derecha">
            <a href="ver_noticia.php?id=<?= $siguiente ?>" class="btn btn-outline-primary">
                Siguiente <i class="fa fa-arrow-right"></i>
            </a>
        </div>
    </div>

    <script>
        function irAnterior() {
            window.location.href = "ver_noticia.php?id=<?= $anterior ?>";
        }

        function irSiguiente() {
            window.location.href = "ver_noticia.php?id=<?= $siguiente ?>";
        }
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        function getCookie(name) {
            let v = document.cookie.match('(^|;) ?' + name + '=([^;]*)(;|$)');
            return v ? v[2] : null;
        }

        function setCookie(name, value, days) {
            let d = new Date();
            d.setTime(d.getTime() + (days * 24 * 60 * 60 * 1000));
            document.cookie = name + "=" + value + ";path=/;expires=" + d.toUTCString();
        }

        // Al cargar, deshabilita si ya dio like
        $(document).ready(function() {
            let noticiaId = <?= $id ?>;
            if (getCookie("like_noticia_" + noticiaId)) {
                $('#like-btn').prop('disabled', true)
                    .removeClass('btn-danger')
                    .addClass('btn-secondary'); // O .addClass('btn-like-disabled')
            }
        });

        $('#like-btn').click(function() {
            let noticiaId = <?= $id ?>;
            if (getCookie("like_noticia_" + noticiaId)) {
                alert('¡Ya diste like!');
                return;
            }

            $.post('like_noticia.php', {
                id: noticiaId
            }, function(data) {
                if (data.success) {
                    $('#like-count').text(data.likes);
                    $('#like-btn').prop('disabled', true)
                        .removeClass('btn-danger')
                        .addClass('btn-secondary'); // O .addClass('btn-like-disabled')
                    setCookie("like_noticia_" + noticiaId, "1", 365);
                } else {
                    alert(data.message);
                }
            }, 'json');
        });
    </script>