<?php
// Destruir la sesión activa del usuario y redirigir al login
session_start();
session_unset();
session_destroy();

header('Location: login.html?logout=ok');
exit;