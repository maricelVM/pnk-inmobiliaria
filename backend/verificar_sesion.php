<?php
// verificar si hay sesion activa si no hay error y redirigir login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['activo']) || $_SESSION['activo'] !== true) {
    header('Location: error_sesion.html');
    exit;
}