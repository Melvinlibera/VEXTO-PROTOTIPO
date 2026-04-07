<?php
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = trim($_POST['mensaje']);
    $tipo = $_POST['tipo'];
    $user_id = $_SESSION['user_id'];

    if (empty($mensaje) || empty($tipo)) {
        $error = 'Todos los campos son obligatorios.';
    } else {
        $stmt = $pdo->prepare("INSERT INTO feedback (user_id, mensaje, tipo) VALUES (?, ?, ?)");
        if ($stmt->execute([$user_id, $mensaje, $tipo])) {
            $success = '¡Gracias por tu feedback! Lo revisaremos pronto.';
        } else {
            $error = 'Error al enviar el feedback.';
        }
    }
}
?>

<div class="feedback-card" style="background: #fff; padding: 2rem; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); max-width: 500px; margin: 2rem auto;">
    <h2 style="text-align: center; margin-bottom: 1.5rem;">Enviar Feedback</h2>
    
    <?php if ($error): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 1rem;"><?php echo $error; ?></div>
    <?php endif; ?>
    
    <?php if ($success): ?>
        <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 1rem;"><?php echo $success; ?></div>
    <?php endif; ?>

    <form action="feedback.php" method="POST">
        <div style="margin-bottom: 1rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Tipo de Feedback</label>
            <select name="tipo" required style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px;">
                <option value="sugerencia">Sugerencia</option>
                <option value="error">Error / Bug</option>
                <option value="mejora">Mejora</option>
            </select>
        </div>
        <div style="margin-bottom: 1.5rem;">
            <label style="display: block; margin-bottom: 0.5rem;">Mensaje</label>
            <textarea name="mensaje" required placeholder="Cuéntanos qué podemos mejorar..." style="width: 100%; height: 150px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; resize: none;"></textarea>
        </div>
        <button type="submit" class="btn" style="width: 100%; background: #007bff; color: #fff; font-weight: 600;">Enviar</button>
    </form>
    <div style="text-align: center; margin-top: 1rem;">
        <a href="index.php" style="color: #666; text-decoration: none;">Volver al inicio</a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
