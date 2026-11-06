<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function seRepresentante() {
    return isset($_SESSION['user_tipo']) 
        && $_SESSION['user_tipo'] === 'representante';
}
?>