<?php
require_once 'includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($nombre) || empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El correo electrónico no es válido.';
    } else {
        // Verificar si el email ya existe
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $error = 'Este correo electrónico ya está registrado.';
        } else {
            // Insertar nuevo usuario
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (nombre, email, password) VALUES (?, ?, ?)");
            if ($stmt->execute([$nombre, $email, $hashed_password])) {
                $success = 'Registro exitoso. Ya puedes <a href="login.php">iniciar sesión</a>.';
            } else {
                $error = 'Error al registrar el usuario.';
            }
        }
    }
}
?>

<div class="auth-card" style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 400px; margin: 2rem auto;">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Crear Cuenta</h2>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 1rem;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 1rem;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="register.php" method="POST">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Nombre</label>
            <input type="text" name="nombre" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Correo Electrónico</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Contraseña</label>
            <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <button type="submit" class="btn" style="width: 100%; background: #007bff; color: #fff; font-weight: 600;">Registrarse</button>
    </form>
    <div style="text-align: center; margin-top: 1rem;">
        ¿Ya tienes cuenta? <a href="login.php" style="color: #007bff; text-decoration: none;">Inicia sesión</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
