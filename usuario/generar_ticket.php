<?php
session_start();
if (!isset($_SESSION['cliente_id']) || !isset($_POST['sorteo_id'])) {
    header("Location: dashboard.php");
    exit();
}

include '../includes/conexion.php';

$clienteId = $_SESSION['cliente_id'];
$sorteoId = intval($_POST['sorteo_id']);

// 1. Verificar si el cliente es afiliado
$stmt = $conexion->prepare("SELECT es_afiliado FROM clientes WHERE id = ?");
$stmt->bind_param("i", $clienteId);
$stmt->execute();
$result = $stmt->get_result();
$cliente = $result->fetch_assoc();

if (!$cliente) {
    header("Location: dashboard.php?error=cliente_no_encontrado");
    exit();
}

$esAfiliado = intval($cliente['es_afiliado']);
$limite = ($esAfiliado === 1) ? 3 : 1;

// 2. Contar cuántos tickets tiene ya este cliente para ese sorteo
$verifica = $conexion->prepare("SELECT COUNT(*) AS total FROM tickets WHERE cliente_id = ? AND sorteo_id = ?");
$verifica->bind_param("ii", $clienteId, $sorteoId);
$verifica->execute();
$res = $verifica->get_result();
$total = $res->fetch_assoc()['total'] ?? 0;

if ($total >= $limite) {
    header("Location: ver_sorteo.php?id=$sorteoId&mensaje=limite");
    exit();
}


// 4. Generar 1 ticket
$codigo = 'TKT' . date('Ymd') . strtoupper(bin2hex(random_bytes(3)));
$insert = $conexion->prepare("INSERT INTO tickets (cliente_id, sorteo_id, codigo_ticket) VALUES (?, ?, ?)");
$insert->bind_param("iis", $clienteId, $sorteoId, $codigo);
$insert->execute();

header("Location: ver_sorteo.php?id=$sorteoId&mensaje=exito");
exit();
?>
