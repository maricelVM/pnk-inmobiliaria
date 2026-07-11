<?php
require_once 'verificar_sesion.php';

if (($_SESSION['rol'] ?? '') !== 'propietario') {
    header('Location: error_sesion.html');
    exit;
}