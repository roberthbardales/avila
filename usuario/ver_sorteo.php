<?php
session_start();
require_once '../includes/conexion.php';

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit();
}

$clienteId = $_SESSION['cliente_id'];
$nombre = $_SESSION['cliente_nombre'] ?? 'Usuario';

if (!isset($_GET['id'])) {
    echo "ID de sorteo no proporcionado.";
    exit();
}

$sorteoId = intval($_GET['id']);

// Consulta optimizada del sorteo + imagen + participantes
$stmt = $conexion->prepare("
    SELECT 
        s.*,
        (SELECT url_imagen FROM imagenes_sorteo WHERE sorteo_id = s.id LIMIT 1) AS url_imagen,
        (SELECT COUNT(DISTINCT cliente_id) FROM tickets WHERE sorteo_id = s.id) AS total_participantes
    FROM sorteos s
    WHERE s.id = ?
");
$stmt->bind_param("i", $sorteoId);
$stmt->execute();
$sorteo = $stmt->get_result()->fetch_assoc();

if (!$sorteo) {
    echo "Sorteo no encontrado.";
    exit();
}

// Afiliación del cliente
$stmtAfiliado = $conexion->prepare("SELECT es_afiliado FROM clientes WHERE id = ?");
$stmtAfiliado->bind_param("i", $clienteId);
$stmtAfiliado->execute();
$esAfiliado = $stmtAfiliado->get_result()->fetch_assoc()['es_afiliado'] ?? 0;

// Puede participar
$permitido = ($sorteo['destinatario'] == 1) ||
    ($sorteo['destinatario'] == 2 && $esAfiliado == 1) ||
    ($sorteo['destinatario'] == 0 && $esAfiliado == 0);

// Tickets generados por este usuario en este sorteo
$stmtTickets = $conexion->prepare("SELECT codigo_ticket, fecha_creacion FROM tickets WHERE cliente_id = ? AND sorteo_id = ?");
$stmtTickets->bind_param("ii", $clienteId, $sorteoId);
$stmtTickets->execute();
$tickets = $stmtTickets->get_result()->fetch_all(MYSQLI_ASSOC);
$ticketCount = count($tickets);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($sorteo['nombre']) ?> - CLEF</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-[#2c5364] text-white font-sans min-h-screen">
     <!-- AQUÍ COLOCA EL MENSAJE -->
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'finalizado'): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
            ⏰ El sorteo ya ha finalizado. No puedes generar más tickets.
        </div>
    <?php endif; ?>

    <div class="max-w-6xl mx-auto mt-8 p-4">
        <!-- Cabecera -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-light italic"><?= htmlspecialchars($sorteo['nombre']) ?></h1>
                <p class="text-xl text-[#98B9C1] mt-[1vh]">Falta <span id="countdown" class="text-blue-300">Calculando...</span></p>
                <p class="text-xl text-[#98B9C1] mt-[1vh]">👥 <?= $sorteo['total_participantes'] ?> Participantes</p>
                <p class="text-xl text-[#98B9C1] mt-[1vh]">🎫 <?= $ticketCount ?> Tickets generados</p>
                <a href="https://meet.google.com/nxy-njph-dyt" target="_blank" class="mt-px bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-semibold">
                LINK MEET- DAME CLICK AQUI
                </a>
            </div>
            <a href="dashboard.php" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md font-semibold">
                ⬅️ REGRESAR
            </a>
        </div>

        <!-- Contenido principal -->
        <div class="bg-[#001D29] rounded-xl p-6 flex flex-col md:flex-row gap-6 shadow-lg">
            <!-- Imagen + botón -->
            <div class="flex-1 text-center">
                <?php if (!empty($sorteo['url_imagen'])): ?>
                    <img src="../<?= htmlspecialchars($sorteo['url_imagen']) ?>" alt="Premio" class="rounded-xl mx-auto mb-4 max-h-60 object-contain" loading="lazy">
                <?php else: ?>
                    <div class="h-60 bg-gray-700 rounded flex items-center justify-center text-gray-300">
                        Sin imagen disponible
                    </div>
                <?php endif; ?>

                <!-- Botón generar -->
                <?php if ($permitido): ?>
                    <?php if (
                        ($esAfiliado == 1 && $ticketCount >= 3) ||
                        ($esAfiliado == 0 && $ticketCount >= 1)
                    ): ?>
                        <div class="mt-4">
                            <?php if ($esAfiliado == 1): ?>
                                <div class="bg-[#004F6F] text-white px-6 py-4 rounded-md font-semibold w-full text-center">
                                    ✅ Ya generaste tus 3 tickets. ¡Mucha suerte!
                                </div>
                            <?php else: ?>
                                <div class="w-full flex justify-center mt-2">
                                    <div class="bg-yellow-600 text-white text-center px-4 py-3 rounded font-semibold max-w-xs w-full leading-snug">
                                        ⚠️ Ya alcanzaste el límite.<br>
                                        <a href="https://wa.me/51946554441?text=Hola,%20quiero%20afiliarme%20para%20participar%20en%20más%20sorteos"
                                            target="_blank" rel="noopener noreferrer"
                                            class="underline text-white">
                                            Afíliate aquí para tener más oportunidades
                                        </a>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <form action="generar_ticket.php" method="POST" class="mt-4">
                            <input type="hidden" name="sorteo_id" value="<?= $sorteoId ?>">
                            <button id="btnGenerar" class="bg-[#004F6F] hover:bg-[#03618c] text-white px-6 py-4 rounded-md font-semibold transition duration-300 w-full">
                                🎟️ GENERAR TICKET
                            </button>
                        </form>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="mt-4">
                        <button class="bg-gray-500 px-6 py-4 rounded-md font-semibold text-white cursor-not-allowed w-full" disabled>
                            🔒 Este sorteo es exclusivo para otro tipo de participantes.
                        </button>
                    </div>
                <?php endif; ?>
            </div>


            <!-- Carrusel de tickets -->
            <div class="flex-1">
                <h3 class="text-lg font-semibold mb-4">🎫 TUS TICKETS PARA EL SORTEO</h3>

                <?php if (count($tickets) > 0): ?>
                    <div class="relative" id="ticket-slider">
                        <div class="overflow-hidden relative h-60">
                            <?php foreach ($tickets as $i => $t): ?>
                                <div class="h-full absolute top-0 left-0 w-full transition-all duration-300 slide <?= $i === 0 ? 'opacity-100' : 'opacity-0' ?>">
                                    <div class="flex flex-col bg-[#0f2533] p-4 rounded-xl h-full justify-around">
                                        <h4 class="text-2xl font-bold mb-2">🎫 Ticket Certificado</h4>
                                        <p class="text-sm text-gray-300">Código de ticket:</p>
                                        <p class="text-xl font-bold tracking-wider"><?= htmlspecialchars($t['codigo_ticket']) ?></p>
                                        <p class="text-sm text-gray-300 mt-2">Fecha de emisión:</p>
                                        <p class="text-white"><?= date("d M Y", strtotime($t['fecha_creacion'])) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <?php if (count($tickets) > 1): ?>
                            <div class="flex justify-center mt-3 gap-2">
                                <button onclick="cambiarSlide(-1)" class="px-2 py-1 bg-gray-600 rounded hover:bg-gray-700">◀</button>
                                <button onclick="cambiarSlide(1)" class="px-2 py-1 bg-gray-600 rounded hover:bg-gray-700">▶</button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="bg-gray-700 p-4 rounded-xl text-center text-white">
                        ⚠️ Aún no has generado tickets para este sorteo.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const fecha = new Date("<?= $sorteo['fecha_sorteo'] ?>");
        const countdown = document.getElementById("countdown");

        function actualizarContador() {
            const ahora = new Date();
            const diff = fecha - ahora;

            if (diff <= 0) {
                countdown.innerText = "🎉 El sorteo ha finalizado";
                return;
            }

            const d = Math.floor(diff / (1000 * 60 * 60 * 24));
            const h = Math.floor((diff / (1000 * 60 * 60)) % 24);
            const m = Math.floor((diff / (1000 * 60)) % 60);
            const s = Math.floor((diff / 1000) % 60);

            countdown.innerText = `${d}d ${h}h ${m}m ${s}s restantes`;
        }

        setInterval(actualizarContador, 1000);
        actualizarContador();

        // Carrusel
        let slideIndex = 0;
        const slides = document.querySelectorAll("#ticket-slider .slide");

        function cambiarSlide(n) {
            slides[slideIndex].classList.remove("opacity-100");
            slides[slideIndex].classList.add("opacity-0");

            slideIndex = (slideIndex + n + slides.length) % slides.length;

            slides[slideIndex].classList.remove("opacity-0");
            slides[slideIndex].classList.add("opacity-100");
        }

        // Mensaje que desaparece
        setTimeout(() => {
            const alerta = document.getElementById("mensajeAlerta");
            if (alerta) {
                alerta.classList.add("opacity-0", "-translate-y-4");
                setTimeout(() => alerta.remove(), 800);
            }
        }, 4000);
    </script>
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'exito'): ?>
        <script>
            const duration = 2 * 1000;
            const end = Date.now() + duration;

            (function frame() {
                confetti({
                    particleCount: 7,
                    angle: 60,
                    spread: 55,
                    origin: {
                        x: 0
                    }
                });
                confetti({
                    particleCount: 7,
                    angle: 120,
                    spread: 55,
                    origin: {
                        x: 1
                    }
                });

                if (Date.now() < end) {
                    requestAnimationFrame(frame);
                }
            })();
        </script>
    <?php endif; ?>
</body>

</html>