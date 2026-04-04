<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

// Obtener datos del vendedor
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$seller = $stmt->fetch();

if (!$seller) die("Vendedor no encontrado.");

// Obtener propiedades del vendedor
$stmt = $pdo->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$id]);
$properties = $stmt->fetchAll();

// Obtener reseñas
$stmt = $pdo->prepare("SELECT r.*, u.nombre, u.apellido FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.seller_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$id]);
$reviews = $stmt->fetchAll();

$reviewDistribution = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
$stmt = $pdo->prepare("SELECT stars, COUNT(*) AS count FROM reviews WHERE seller_id = ? GROUP BY stars");
$stmt->execute([$id]);
foreach ($stmt->fetchAll() as $row) {
    $reviewDistribution[(int)$row['stars']] = (int)$row['count'];
}

$reviewStats = ['avg_rating' => 0, 'total' => 0];
$stmt = $pdo->prepare("SELECT AVG(stars) AS avg_rating, COUNT(*) AS total FROM reviews WHERE seller_id = ?");
$stmt->execute([$id]);
$reviewStats = $stmt->fetch();
$reviewStats['avg_rating'] = $reviewStats['avg_rating'] ? floatval($reviewStats['avg_rating']) : 0;
$reviewStats['total'] = intval($reviewStats['total']);

$userReview = null;
foreach ($reviews as $rev) {
    if ($rev['reviewer_id'] == $user_id) {
        $userReview = $rev;
        break;
    }
}
?>

<div class="main-container" style="flex-direction: column; max-width: 1200px; margin: 40px auto;">
    <div class="filter-card" style="width: 100%; position: static; display: flex; align-items: center; gap: 40px; padding: 50px; margin-bottom: 40px;">
        <div class="seller-avatar-mini" style="width: 150px; height: 150px;"></div>
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 10px;">
                <h1 style="font-size: 2.5rem; font-weight: 900;"><?php echo htmlspecialchars($seller['nombre'] . ' ' . $seller['apellido']); ?></h1>
                <?php if ($seller['tipo_usuario'] == 'compania'): ?>
                    <span class="badge-pill" style="padding: 5px 15px; font-size: 0.8rem;">COMPAÑÍA VERIFICADA</span>
                <?php endif; ?>
            </div>
            <div style="font-size: 1.2rem; margin-bottom: 15px;">
                <i class="fas fa-star"></i> <?php echo number_format($reviewStats['avg_rating'], 1); ?> 
                <span style="font-size: 0.9rem; color: var(--muted-text);">(<?php echo $reviewStats['total']; ?> reseñas)</span>
            </div>
            <p style="font-size: 1.1rem; color: var(--muted-text); max-width: 800px;"><?php echo nl2br(htmlspecialchars($seller['bio'] ?: 'Sin biografía disponible.')); ?></p>
        </div>
    </div>

    <div style="display: flex; gap: 30px; border-bottom: 2px solid var(--border-color); margin-bottom: 30px;">
        <div id="tab-props" class="tab active" onclick="switchTab('props')" style="padding: 15px 30px; cursor: pointer; font-weight: 800; border-bottom: 4px solid var(--accent-color);">Proyectos Activos (<?php echo count($properties); ?>)</div>
        <div id="tab-reviews" class="tab" onclick="switchTab('reviews')" style="padding: 15px 30px; cursor: pointer; font-weight: 800; color: var(--muted-text);">Reseñas de Clientes (<?php echo count($reviews); ?>)</div>
    </div>

    <!-- Sección de Propiedades -->
    <div id="content-props" class="content-grid">
        <?php if (empty($properties)): ?>
            <p style="grid-column: 1/-1; text-align: center; padding: 40px; color: var(--muted-text);">Este vendedor no tiene propiedades activas.</p>
        <?php else: ?>
            <?php foreach ($properties as $prop): ?>
                <?php
                    $imageSrc = getPropertyImageUrl($prop['imagen_url']);
                    if (!empty($prop['imagen'])) {
                        $imageSrc = 'data:' . htmlspecialchars($prop['imagen_tipo']) . ';base64,' . base64_encode($prop['imagen']);
                    }
                ?>
                <div class="property-card" onclick="location.href='property_details.php?id=<?php echo $prop['id']; ?>'">
                    <div class="property-img-container">
                        <span class="op-badge"><?php echo $prop['tipo_operacion']; ?></span>
                        <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="property-img" alt="Propiedad">
                    </div>
                    <div class="property-info">
                        <div class="property-price">$<?php echo number_format($prop['precio'], 2); ?></div>
                        <div class="property-title"><?php echo htmlspecialchars($prop['titulo']); ?></div>
                        <div class="property-meta">
                            <span><i class="fas fa-bed"></i> <?php echo $prop['habitaciones']; ?></span>
                            <span><i class="fas fa-bath"></i> <?php echo $prop['banos']; ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop['ubicacion']); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- Sección de Reseñas -->
    <div id="content-reviews" style="display: none; flex-direction: column; gap: 24px;">
        <div class="review-summary">
            <div class="rating-card">
                <h4>Calificación promedio</h4>
                <div class="rating-value">
                    <?php echo number_format($reviewStats['avg_rating'], 1); ?>
                    <span>(<?php echo $reviewStats['total']; ?> reseñas)</span>
                </div>
                <div class="rating-subtitle">Valoraciones reales de usuarios que contrataron o consultaron.</div>
            </div>
            <div class="rating-card">
                <h4>Distribución de estrellas</h4>
                <div class="rating-bars">
                    <?php foreach ([5, 4, 3, 2, 1] as $star):
                        $count = $reviewDistribution[$star];
                        $percent = $reviewStats['total'] ? round(($count / $reviewStats['total']) * 100) : 0;
                    ?>
                        <div class="rating-bar">
                            <span><?php echo $star; ?>★</span>
                            <div class="bar-bg"><div class="bar-fill" style="width: <?php echo $percent; ?>%;"></div></div>
                            <span><?php echo $count; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if ($id != $user_id): ?>
            <div class="review-form-card">
                <h4><?php echo $userReview ? 'Editar tu reseña' : 'Dejar una reseña'; ?></h4>
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="add_review">
                    <input type="hidden" name="seller_id" value="<?php echo $id; ?>">
                    <input type="hidden" name="redirect_to" value="profile.php?id=<?php echo $id; ?>">
                    <div class="filter-group">
                        <label>Calificación:</label>
                        <select name="stars" required>
                            <option value="5" <?php echo ($userReview['stars'] ?? 5) == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 Estrellas)</option>
                            <option value="4" <?php echo ($userReview['stars'] ?? '') == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 Estrellas)</option>
                            <option value="3" <?php echo ($userReview['stars'] ?? '') == 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3 Estrellas)</option>
                            <option value="2" <?php echo ($userReview['stars'] ?? '') == 2 ? 'selected' : ''; ?>>⭐⭐ (2 Estrellas)</option>
                            <option value="1" <?php echo ($userReview['stars'] ?? '') == 1 ? 'selected' : ''; ?>>⭐ (1 Estrella)</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label>Comentario:</label>
                        <textarea name="comment" rows="5" placeholder="Cuéntanos tu experiencia con este vendedor..." required><?php echo htmlspecialchars($userReview['comment'] ?? ''); ?></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo $userReview ? 'Actualizar Reseña' : 'Publicar Reseña'; ?></button>
                </form>
            </div>
        <?php endif; ?>

        <?php if (empty($reviews)): ?>
            <p style="text-align: center; padding: 40px; color: var(--muted-text);">Aún no hay reseñas para este vendedor.</p>
        <?php else: ?>
            <?php foreach ($reviews as $rev): ?>
                <?php $initials = strtoupper(substr($rev['nombre'], 0, 1) . substr($rev['apellido'], 0, 1)); ?>
                <div class="review-card">
                    <div class="review-card-header">
                        <div class="review-author">
                            <div class="review-author-avatar"><?php echo htmlspecialchars($initials); ?></div>
                            <div>
                                <div class="review-author-name"><?php echo htmlspecialchars($rev['nombre'] . ' ' . $rev['apellido']); ?></div>
                                <div class="review-meta-note">Publicado el <?php echo formatDate($rev['created_at']); ?></div>
                            </div>
                        </div>
                        <div class="review-stars"><?php for ($i = 0; $i < $rev['stars']; $i++) echo '★'; ?></div>
                    </div>
                    <p><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>

                    <?php if ($rev['reviewer_id'] == $user_id): ?>
                        <button type="button" class="btn btn-outline" style="margin-top: 18px; padding: 10px 18px;" onclick="toggleReviewEdit(<?php echo $rev['id']; ?>)">Editar reseña</button>
                        <div id="edit-review-<?php echo $rev['id']; ?>" style="display: none; margin-top: 18px;">
                            <div class="review-form-card" style="padding: 20px;">
                                <form action="actions.php" method="POST">
                                    <input type="hidden" name="action" value="add_review">
                                    <input type="hidden" name="seller_id" value="<?php echo $id; ?>">
                                    <input type="hidden" name="redirect_to" value="profile.php?id=<?php echo $id; ?>">
                                    <div class="filter-group">
                                        <label>Calificación:</label>
                                        <select name="stars" required>
                                            <option value="5" <?php echo $rev['stars'] == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 Estrellas)</option>
                                            <option value="4" <?php echo $rev['stars'] == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 Estrellas)</option>
                                            <option value="3" <?php echo $rev['stars'] == 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3 Estrellas)</option>
                                            <option value="2" <?php echo $rev['stars'] == 2 ? 'selected' : ''; ?>>⭐⭐ (2 Estrellas)</option>
                                            <option value="1" <?php echo $rev['stars'] == 1 ? 'selected' : ''; ?>>⭐ (1 Estrella)</option>
                                        </select>
                                    </div>
                                    <div class="filter-group">
                                        <label>Comentario:</label>
                                        <textarea name="comment" rows="4" required><?php echo htmlspecialchars($rev['comment']); ?></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function switchTab(tab) {
    const propsTab = document.getElementById('tab-props');
    const reviewsTab = document.getElementById('tab-reviews');
    const propsContent = document.getElementById('content-props');
    const reviewsContent = document.getElementById('content-reviews');

    if (tab === 'props') {
        propsTab.style.borderBottom = '4px solid var(--accent-color)';
        propsTab.style.color = 'var(--text-color)';
        reviewsTab.style.borderBottom = 'none';
        reviewsTab.style.color = 'var(--muted-text)';
        propsContent.style.display = 'grid';
        reviewsContent.style.display = 'none';
    } else {
        reviewsTab.style.borderBottom = '4px solid var(--accent-color)';
        reviewsTab.style.color = 'var(--text-color)';
        propsTab.style.borderBottom = 'none';
        propsTab.style.color = 'var(--muted-text)';
        propsContent.style.display = 'none';
        reviewsContent.style.display = 'flex';
    }
}

function toggleReviewEdit(reviewId) {
    const el = document.getElementById('edit-review-' + reviewId);
    if (el) {
        el.style.display = el.style.display === 'none' ? 'block' : 'none';
    }
}
</script>

</body>
</html>
