<?php
// includes/auth.php

if (!defined('AUTH_INCLUDED')) {
    define('AUTH_INCLUDED', true);
    
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    function isAdmin() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] == 'Administrador';
    }

    function isTecnico() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] == 'Tecnico';
    }

    function isAdminOrTecnico() {
        return isset($_SESSION['rol']) && ($_SESSION['rol'] == 'Administrador' || $_SESSION['rol'] == 'Tecnico');
    }

    function isUsuario() {
        return isset($_SESSION['rol']) && $_SESSION['rol'] == 'Usuario';
    }

    function redirectIfNotLoggedIn() {
        if (!isLoggedIn()) {
            header("Location: ../login.php");
            exit();
        }
    }

    function redirectIfNotAdmin() {
        redirectIfNotLoggedIn();
        if (!isAdmin()) {
            header("Location: ../user/dashboard.php");
            exit();
        }
    }

    function redirectIfNotTecnico() {
        redirectIfNotLoggedIn();
        if (!isTecnico()) {
            header("Location: ../user/dashboard.php");
            exit();
        }
    }

    function redirectIfNotAdminOrTecnico() {
        redirectIfNotLoggedIn();
        if (!isAdminOrTecnico()) {
            header("Location: ../user/dashboard.php");
            exit();
        }
    }

    function getUserRole() {
        return $_SESSION['rol'] ?? null;
    }

    function getUserName() {
        return $_SESSION['user_name'] ?? 'Usuario';
    }

    function getUserId() {
        return $_SESSION['user_id'] ?? null;
    }
}
?>