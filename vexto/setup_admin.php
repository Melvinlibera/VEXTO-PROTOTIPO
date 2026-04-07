<?php
require_once 'includes/db.php';

$nombre = 'admin';
$email = 'admin@vexto.com';
$password = 'Dragon502';
$rol = 'admin';

$hashed_password = password_hash($password, PASSWORD_DEFAULT);

try {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo "El usuario admin ya existe.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (nombre, email, password, rol) VALUES (?, ?, ?, ?)");
        if ($stmt->execute([$nombre, $email, $hashed_password, $rol])) {
            echo "Usuario admin creado exitosamente.<br>";
            echo "Usuario: admin@vexto.com<br>";
            echo "Contraseña: Dragon502";
        } else {
            echo "Error al crear el usuario admin.";
        }
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
