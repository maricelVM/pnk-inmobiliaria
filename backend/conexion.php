<?php

$server = 'localhost';
$bd     = 'pnk_inmobiliaria';
$user   = 'root';
$pass   = '123456';

$conexion = @new mysqli($server, $user, $pass, $bd);

if ($conexion->connect_error) {
    echo "Error: " . $conexion->connect_error . "------";
    exit();
}

$conexion->set_charset('utf8mb4');

//echo "<div class='alert alert-success'>Conexión exitosa!</div>";