<?php
require_once 'includes/header.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $stmt = $pdo->prepare("SELECT id, nombre, password, rol FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Credenciales incorrectas.';
        }
    }
}
?>

<div class="auth-card" style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 400px; margin: 2rem auto;">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Iniciar Sesión</h2>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 1rem;"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Correo Electrónico</label>
            <input type="email" name="email" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Contraseña</label>
            <input type="password" name="password" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
        </div>
        <button type="submit" class="btn" style="width: 100%; background: #007bff; color: #fff; font-weight: 600;">Entrar</button>
    </form>
    <div style="text-align: center; margin-top: 1rem;">
        ¿No tienes cuenta? <a href="register.php" style="color: #007bff; text-decoration: none;">Regístrate aquí</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
