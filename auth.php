<?php
session_start();
require_once 'conexion.php';

function protegerRuta() {
    if (!isset($_SESSION['usuario'])) {
        header('Location: login.php');
        exit;
    }
}

function soloAdmin() {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
        header('Location: index.php');
        exit;
    }
}

function soloCliente() {
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'cliente') {
        header('Location: index.php');
        exit;
    }
}

function limpiarTexto($texto) {
    return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
}
