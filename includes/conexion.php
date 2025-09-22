<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$conexion = new mysqli("c1105690.sgvps.net", "ub9fdeq1qmouw", "1@1~12#112u4", "dbcnjsv3vx3ltg");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>
