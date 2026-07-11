<?php
require_once 'verificar_sesion.php';

$rol = $_SESSION['rol'] ?? '';

if ($rol === 'administrador') {
    header('Location: administracion.php');
    exit;
}

if ($rol === 'propietario') {
    header('Location: panel_propietario.php');
    exit;
}

if ($rol === 'gestor') {
    header('Location: panel_gestor.php');
    exit;
}

header('Location: error_sesion.html');
exit;
