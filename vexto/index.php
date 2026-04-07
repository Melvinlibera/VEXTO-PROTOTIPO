<?php
require_once 'includes/header.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Procesar nueva publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['post_content'])) {
    // Sanitizar entrada
    $contenido = htmlspecialchars(trim($_POST['post_content']), ENT_QUOTES, 'UTF-8');
    if (!empty($contenido)) {
        $stmt = $pdo->prepare("INSERT INTO posts (user_id, contenido) VALUES (?, ?)");
        $stmt->execute([$user_id, $contenido]);
        header('Location: index.php');
        exit;
    }
}

// Procesar nuevo comentario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment_content'])) {
    $post_id = (int)$_POST['post_id'];
    $comentario = htmlspecialchars(trim($_POST['comment_content']), ENT_QUOTES, 'UTF-8');
    if (!empty($comentario)) {
        $stmt = $pdo->prepare("INSERT INTO comments (post_id, user_id, comentario) VALUES (?, ?, ?)");
        $stmt->execute([$post_id, $user_id, $comentario]);
        header('Location: index.php');
        exit;
    }
}

// Obtener publicaciones
$stmt = $pdo->query("SELECT p.*, u.nombre FROM posts p JOIN users u ON p.user_id = u.id ORDER BY p.fecha DESC");
$posts = $stmt->fetchAll();
?>

<div class="feed-container">
    <div class="publish-card" style="background: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 2rem;">
        <h3>¿Qué estás pensando, <?php echo htmlspecialchars($_SESSION['nombre']); ?>?</h3>
        <form action="index.php" method="POST">
            <textarea name="post_content" required placeholder="Escribe algo..." style="width: 100%; height: 100px; padding: 10px; border: 1px solid #ddd; border-radius: 5px; margin: 1rem 0; resize: none;"></textarea>
            <button type="submit" class="btn" style="background: #007bff; color: #fff; font-weight: 600;">Publicar</button>
        </form>
    </div>

    <div class="posts-list">
        <?php foreach ($posts as $post): ?>
            <div class="post-card" style="background: #fff; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 1.5rem;">
                <div class="post-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <span style="font-weight: 700; color: #007bff;"><?php echo htmlspecialchars($post['nombre']); ?></span>
                    <small style="color: #999;"><?php echo date('d/m/Y H:i', strtotime($post['fecha'])); ?></small>
                </div>
                <div class="post-content" style="margin-bottom: 1.5rem; line-height: 1.6;">
                    <?php echo nl2br(htmlspecialchars($post['contenido'])); ?>
                </div>
                
                <div class="comments-section" style="border-top: 1px solid #eee; padding-top: 1rem;">
                    <h4 style="font-size: 14px; margin-bottom: 1rem;">Comentarios</h4>
                    <?php
                    $stmt_c = $pdo->prepare("SELECT c.*, u.nombre FROM comments c JOIN users u ON c.user_id = u.id WHERE c.post_id = ? ORDER BY c.fecha ASC");
                    $stmt_c->execute([$post['id']]);
                    $comments = $stmt_c->fetchAll();
                    foreach ($comments as $comment):
                    ?>
                        <div class="comment" style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-bottom: 0.5rem; font-size: 14px;">
                            <strong style="color: #333;"><?php echo htmlspecialchars($comment['nombre']); ?>:</strong>
                            <span><?php echo htmlspecialchars($comment['comentario']); ?></span>
                        </div>
                    <?php endforeach; ?>
                    
                    <form action="index.php" method="POST" style="margin-top: 1rem; display: flex; gap: 10px;">
                        <input type="hidden" name="post_id" value="<?php echo $post['id']; ?>">
                        <input type="text" name="comment_content" required placeholder="Escribe un comentario..." style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                        <button type="submit" class="btn" style="background: #28a745; color: #fff; font-size: 12px;">Comentar</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
        
        <?php if (empty($posts)): ?>
            <p style="text-align: center; color: #666;">No hay publicaciones aún. ¡Sé el primero en publicar!</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
