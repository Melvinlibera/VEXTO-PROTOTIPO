<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['rol']) && $_SESSION['rol'] === 'admin';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>VEXTO - Red Social</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/style_enhanced.css">
    <style>
        /* Ajustes de diseño solicitados */
        body { font-size: 16px; }
        h1, h2, h3 { font-size: 24px; }
        .btn { padding: 8px 14px; border-radius: 5px; cursor: pointer; border: none; }
        .navbar { display: flex; justify-content: space-between; align-items: center; padding: 1rem 2rem; background: #fff; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .nav-links a { margin-left: 1rem; text-decoration: none; color: #333; }
        .feedback-btn { position: fixed; bottom: 20px; right: 20px; background: #007bff; color: #fff; padding: 10px 15px; border-radius: 50px; text-decoration: none; box-shadow: 0 4px 6px rgba(0,0,0,0.1); z-index: 1000; }
        @media (max-width: 768px) {
            .navbar { flex-direction: column; }
            .nav-links { margin-top: 1rem; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <a href="index.php" style="font-weight: 900; font-size: 24px; color: #007bff; text-decoration: none;">VEXTO</a>
        </div>
        <div class="nav-links">
            <?php if (isLoggedIn()): ?>
                <a href="index.php">Inicio</a>
                <?php if (isAdmin()): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="login.php">Iniciar Sesión</a>
                <a href="register.php">Registrarse</a>
            <?php endif; ?>
        </div>
    </nav>
    <div class="container" style="max-width: 800px; margin: 2rem auto; padding: 0 1rem;">
