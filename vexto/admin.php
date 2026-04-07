<?php
require_once 'includes/header.php';

if (!isAdmin()) {
    header('Location: index.php');
    exit;
}

// Acciones de administración
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    if ($action === 'delete_user' && $id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'delete_post') {
        $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
        $stmt->execute([$id]);
    } elseif ($action === 'delete_comment') {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$id]);
    }
    header('Location: admin.php');
    exit;
}

// Obtener datos para el panel
$users = $pdo->query("SELECT * FROM users ORDER BY fecha DESC")->fetchAll();
$posts = $pdo->query("SELECT p.*, u.nombre FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.fecha DESC")->fetchAll();
$feedbacks = $pdo->query("SELECT f.*, u.nombre FROM feedback f JOIN users u ON f.user_id = u.id ORDER BY f.fecha DESC")->fetchAll();
?>

<div class="admin-panel">
    <h2 style="margin-bottom: 2rem; text-align: center; color: #007bff;">Panel de Administración</h2>

    <!-- Gestión de Usuarios -->
    <div class="admin-section" style="background: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 2rem;">
        <h3>Usuarios</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Nombre</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Email</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Rol</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user['nombre']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user['email']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($user['rol']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                <a href="admin.php?action=delete_user&id=<?php echo $user['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar este usuario?')" style="color: #dc3545; text-decoration: none; font-size: 14px;">Eliminar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gestión de Publicaciones -->
    <div class="admin-section" style="background: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 2rem;">
        <h3>Publicaciones</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Usuario</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Contenido</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($post['nombre']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo substr(htmlspecialchars($post['contenido']), 0, 50) . '...'; ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;">
                            <a href="admin.php?action=delete_post&id=<?php echo $post['id']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta publicación?')" style="color: #dc3545; text-decoration: none; font-size: 14px;">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gestión de Feedback -->
    <div class="admin-section" style="background: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05);">
        <h3>Feedback Recibido</h3>
        <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
            <thead>
                <tr style="background: #f8f9fa; text-align: left;">
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Usuario</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Tipo</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Mensaje</th>
                    <th style="padding: 10px; border-bottom: 1px solid #eee;">Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($feedbacks as $fb): ?>
                    <tr>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($fb['nombre']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($fb['tipo']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo htmlspecialchars($fb['mensaje']); ?></td>
                        <td style="padding: 10px; border-bottom: 1px solid #eee;"><?php echo date('d/m/Y', strtotime($fb['fecha'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
