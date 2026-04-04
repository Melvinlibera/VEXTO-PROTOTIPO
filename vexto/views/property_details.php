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

// Incrementar vistas
$stmt = $pdo->prepare("UPDATE properties SET vistas = vistas + 1 WHERE id = ?");
$stmt->execute([$id]);

// Obtener detalles
$stmt = $pdo->prepare("SELECT p.*, u.nombre, u.apellido, u.rating, u.total_reviews, u.foto_perfil, u.tipo_usuario, u.bio 
                      FROM properties p 
                      JOIN users u ON p.user_id = u.id 
                      WHERE p.id = ?");
$stmt->execute([$id]);
$prop = $stmt->fetch();

if (!$prop) die("Propiedad no encontrada.");

// Verificar si es favorito
$stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND property_id = ?");
$stmt->execute([$user_id, $id]);
$is_fav = $stmt->fetch();

// Obtener reseñas del vendedor y la reseña del usuario actual
$stmt = $pdo->prepare("SELECT r.*, u.nombre, u.apellido FROM reviews r JOIN users u ON r.reviewer_id = u.id WHERE r.seller_id = ? ORDER BY r.created_at DESC");
$stmt->execute([$prop['user_id']]);
$property_reviews = $stmt->fetchAll();

$reviewStats = ['avg_rating' => 0, 'total' => 0];
$stmt = $pdo->prepare("SELECT AVG(stars) AS avg_rating, COUNT(*) AS total FROM reviews WHERE seller_id = ?");
$stmt->execute([$prop['user_id']]);
$reviewStats = $stmt->fetch();
$reviewStats['avg_rating'] = $reviewStats['avg_rating'] ? floatval($reviewStats['avg_rating']) : 0;
$reviewStats['total'] = intval($reviewStats['total']);

$stmt = $pdo->prepare("SELECT * FROM reviews WHERE seller_id = ? AND reviewer_id = ?");
$stmt->execute([$prop['user_id'], $user_id]);
$user_review = $stmt->fetch();
?>

<div class="main-container" style="flex-direction: column; max-width: 1200px; margin: 40px auto;">
    <?php
        $imageSrc = getPropertyImageUrl($prop['imagen_url']);
        if (!empty($prop['imagen'])) {
            $imageSrc = 'data:' . htmlspecialchars($prop['imagen_tipo']) . ';base64,' . base64_encode($prop['imagen']);
        }
    ?>
    <div class="property-img-container" style="height: 500px; border-radius: 12px; margin-bottom: 30px;">
        <span class="op-badge" style="font-size: 1rem; padding: 8px 20px;"><?php echo $prop['tipo_operacion']; ?></span>
        <img src="<?php echo htmlspecialchars($imageSrc); ?>" class="property-img" alt="Propiedad">
    </div>

    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 40px;">
        <div class="info-section">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h1 style="font-size: 2.5rem; font-weight: 900;"><?php echo htmlspecialchars($prop['titulo']); ?></h1>
                    <p style="font-size: 1.2rem; color: var(--muted-text);"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop['ubicacion']); ?></p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 2.5rem; font-weight: 900;">$<?php echo number_format($prop['precio'], 2); ?></div>
                    <p style="font-size: 0.9rem; color: var(--muted-text);">Precio de <?php echo $prop['tipo_operacion']; ?></p>
                </div>
            </div>

            <div style="display: flex; gap: 30px; border-top: 1px solid var(--border-color); border-bottom: 1px solid var(--border-color); padding: 20px 0; margin-bottom: 30px;">
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $prop['habitaciones']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--muted-text); text-transform: uppercase;">Habitaciones</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $prop['banos']; ?></div>
                    <div style="font-size: 0.8rem; color: var(--muted-text); text-transform: uppercase;">Baños</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo $prop['area_m2'] ?: 'N/A'; ?></div>
                    <div style="font-size: 0.8rem; color: var(--muted-text); text-transform: uppercase;">Área m²</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800;"><?php echo ucfirst($prop['tipo_propiedad']); ?></div>
                    <div style="font-size: 0.8rem; color: var(--muted-text); text-transform: uppercase;">Tipo</div>
                </div>
            </div>

            <div style="margin-bottom: 40px;">
                <h3 style="margin-bottom: 15px; font-size: 1.5rem; font-weight: 800;">Descripción del Proyecto</h3>
                <p style="font-size: 1.1rem; line-height: 1.8; color: var(--text-color);"><?php echo nl2br(htmlspecialchars($prop['descripcion'])); ?></p>
            </div>

            <div style="margin-bottom: 40px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; gap: 24px;">
                    <div>
                        <h3 style="margin: 0; font-size: 1.5rem; font-weight: 800;">Reseñas del Vendedor</h3>
                        <p style="margin: 6px 0 0; color: var(--muted-text); font-size: 0.95rem;">Valora al vendedor y ayuda a otros usuarios con tu experiencia.</p>
                    </div>
                    <div style="font-size: 1rem; color: #f59e0b; font-weight: 700; display: flex; align-items: center; gap: 10px;">
                        <?php echo number_format($reviewStats['avg_rating'], 1); ?> <i class="fas fa-star"></i>
                        <span style="font-size: 0.9rem; color: var(--muted-text); font-weight: 600;">(<?php echo $reviewStats['total']; ?> reseñas)</span>
                    </div>
                </div>

                <?php if ($prop['user_id'] != $user_id): ?>
                    <div class="review-form-card" style="margin-bottom: 25px;">
                        <h4 style="margin-bottom: 18px; font-weight: 800;"><?php echo $user_review ? 'Editar tu reseña' : 'Dejar una reseña'; ?></h4>
                        <form action="actions.php" method="POST">
                            <input type="hidden" name="action" value="add_review">
                            <input type="hidden" name="seller_id" value="<?php echo $prop['user_id']; ?>">
                            <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                            <input type="hidden" name="redirect_to" value="property_details.php?id=<?php echo $id; ?>">
                            <div class="filter-group">
                                <label>Calificación:</label>
                                <select name="stars" required>
                                    <option value="5" <?php echo ($user_review['stars'] ?? 5) == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ 5 Estrellas</option>
                                    <option value="4" <?php echo ($user_review['stars'] ?? '') == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ 4 Estrellas</option>
                                    <option value="3" <?php echo ($user_review['stars'] ?? '') == 3 ? 'selected' : ''; ?>>⭐⭐⭐ 3 Estrellas</option>
                                    <option value="2" <?php echo ($user_review['stars'] ?? '') == 2 ? 'selected' : ''; ?>>⭐⭐ 2 Estrellas</option>
                                    <option value="1" <?php echo ($user_review['stars'] ?? '') == 1 ? 'selected' : ''; ?>>⭐ 1 Estrella</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label>Comentario:</label>
                                <textarea name="comment" rows="4" placeholder="Cuéntanos tu experiencia con este vendedor..." required><?php echo htmlspecialchars($user_review['comment'] ?? ''); ?></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary"><?php echo $user_review ? 'Actualizar Reseña' : 'Publicar Reseña'; ?></button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if (empty($property_reviews)): ?>
                    <div class="review-card" style="text-align:center; color:var(--muted-text);">Aún no hay reseñas para este vendedor.</div>
                <?php else: ?>
                    <div style="display: grid; gap: 20px;">
                        <?php foreach ($property_reviews as $review): ?>
                            <?php $initials = strtoupper(substr($review['nombre'], 0, 1) . substr($review['apellido'], 0, 1)); ?>
                            <div class="review-card">
                                <div class="review-card-header">
                                    <div class="review-author">
                                        <div class="review-author-avatar"><?php echo htmlspecialchars($initials); ?></div>
                                        <div>
                                            <div class="review-author-name"><?php echo htmlspecialchars($review['nombre'] . ' ' . $review['apellido']); ?></div>
                                            <div class="review-meta-note">Publicado el <?php echo formatDate($review['created_at']); ?></div>
                                        </div>
                                    </div>
                                    <div class="review-stars"><?php for ($i = 0; $i < $review['stars']; $i++): ?>★<?php endfor; ?></div>
                                </div>
                                <p><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                                <?php if ($review['reviewer_id'] == $user_id): ?>
                                    <button type="button" class="btn btn-outline" style="margin-top: 18px; padding: 10px 18px;" onclick="toggleReviewEdit(<?php echo $review['id']; ?>)">Editar reseña</button>
                                    <div id="edit-review-<?php echo $review['id']; ?>" style="display: none; margin-top: 18px;">
                                        <div class="review-form-card" style="padding: 20px;">
                                            <form action="actions.php" method="POST">
                                                <input type="hidden" name="action" value="add_review">
                                                <input type="hidden" name="seller_id" value="<?php echo $prop['user_id']; ?>">
                                                <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                                                <input type="hidden" name="redirect_to" value="property_details.php?id=<?php echo $id; ?>">
                                                <div class="filter-group">
                                                    <label>Calificación:</label>
                                                    <select name="stars" required>
                                                        <option value="5" <?php echo $review['stars'] == 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ 5 Estrellas</option>
                                                        <option value="4" <?php echo $review['stars'] == 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ 4 Estrellas</option>
                                                        <option value="3" <?php echo $review['stars'] == 3 ? 'selected' : ''; ?>>⭐⭐⭐ 3 Estrellas</option>
                                                        <option value="2" <?php echo $review['stars'] == 2 ? 'selected' : ''; ?>>⭐⭐ 2 Estrellas</option>
                                                        <option value="1" <?php echo $review['stars'] == 1 ? 'selected' : ''; ?>>⭐ 1 Estrella</option>
                                                    </select>
                                                </div>
                                                <div class="filter-group">
                                                    <label>Comentario:</label>
                                                    <textarea name="comment" rows="4" required><?php echo htmlspecialchars($review['comment']); ?></textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary">Guardar cambios</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div style="margin-bottom: 40px;">
                <h3 style="margin-bottom: 15px; font-size: 1.5rem; font-weight: 800;">Ubicación Exacta</h3>
                <div id="map" style="height: 400px;"></div>
            </div>
        </div>

        <aside class="actions-sidebar">
            <div class="filter-card" style="position: sticky; top: 100px;">
                <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 25px; cursor: pointer;" onclick="location.href='profile.php?id=<?php echo $prop['user_id']; ?>'">
                    <div class="seller-avatar-mini" style="width: 60px; height: 60px;"></div>
                    <div>
                        <div style="font-weight: 800; font-size: 1.1rem;"><?php echo htmlspecialchars($prop['nombre'] . ' ' . $prop['apellido']); ?></div>
                        <div style="font-size: 0.85rem; color: var(--muted-text);">
                            <?php if ($prop['tipo_usuario'] == 'compania'): ?>
                                <span class="badge-pill" style="font-size: 0.7rem; margin-right: 5px;">COMPAÑÍA</span>
                            <?php endif; ?>
                            <i class="fas fa-star"></i> <?php echo number_format($reviewStats['avg_rating'], 1); ?> (<?php echo $reviewStats['total']; ?> reseñas)
                        </div>
                    </div>
                </div>

                <?php if ($prop['user_id'] != $user_id): ?>
                    <button class="btn btn-primary" style="width: 100%; padding: 15px; margin-bottom: 15px;" onclick="openChatModal()">
                        <i class="fas fa-comment-dots"></i> Contactar Persona
                    </button>
                    <button class="btn btn-outline" style="width: 100%; padding: 15px; margin-bottom: 15px;" onclick="openModal()">
                        <i class="fas fa-calendar-alt"></i> Agendar Cita
                    </button>
                <?php else: ?>
                    <button class="btn btn-primary" style="width: 100%; padding: 15px; margin-bottom: 15px;" onclick="location.href='publish.php?edit=<?php echo $id; ?>'">
                        <i class="fas fa-edit"></i> Editar Publicación
                    </button>
                <?php endif; ?>
                
                <form action="actions.php" method="POST">
                    <input type="hidden" name="action" value="toggle_favorite">
                    <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                    <button type="submit" class="btn btn-outline" style="width: 100%; padding: 15px; margin-bottom: 15px;">
                        <i class="<?php echo $is_fav ? 'fas' : 'far'; ?> fa-heart" style="color: <?php echo $is_fav ? '#ff4444' : 'inherit'; ?>;"></i> 
                        <?php echo $is_fav ? 'Guardado en Favoritos' : 'Guardar en Favoritos'; ?>
                    </button>
                </form>

                <button class="btn" style="width: 100%; color: var(--muted-text); font-size: 0.8rem; background: transparent; border: 1px solid var(--border-color); margin-top: 10px;" onclick="reportPost()">
                    <i class="fas fa-flag"></i> Reportar Publicación
                </button>
            </div>
        </aside>
    </div>
</div>

<!-- Modal Cita -->
<div id="appointmentModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(5px);">
    <div class="filter-card" style="width: 400px; position: static; animation: scaleIn 0.3s ease;">
        <h2 style="margin-bottom: 20px; font-weight: 900;">AGENDAR CITA</h2>
        <form action="actions.php" method="POST">
            <input type="hidden" name="action" value="schedule_appointment">
            <input type="hidden" name="property_id" value="<?php echo $id; ?>">
            <input type="hidden" name="seller_id" value="<?php echo $prop['user_id']; ?>">
            <div class="filter-group">
                <label>Selecciona Fecha y Hora:</label>
                <input type="datetime-local" name="fecha" required style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--border-color);">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 10px; padding: 15px;">Confirmar Cita</button>
            <button type="button" class="btn btn-outline" style="width: 100%; padding: 12px;" onclick="closeModal()">Cancelar</button>
        </form>
    </div>
</div>

<!-- Modal Chat Rápido -->
<div id="chatModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 2000; justify-content: center; align-items: center; backdrop-filter: blur(5px);">
    <div class="filter-card" style="width: 450px; position: static; animation: scaleIn 0.3s ease;">
        <h2 style="margin-bottom: 10px; font-weight: 900;">CONTACTAR PERSONA</h2>
        <p style="font-size: 0.9rem; color: var(--muted-text); margin-bottom: 20px;">Escribe un mensaje directamente a la persona responsable de esta publicación.</p>
        <form id="quick-chat-form">
            <input type="hidden" name="property_id" value="<?php echo $id; ?>">
            <input type="hidden" name="receiver_id" value="<?php echo $prop['user_id']; ?>">
            <div class="filter-group">
                <textarea name="message" placeholder="Hola, me interesa esta propiedad, ¿puedes darme más detalles?" required 
                          style="width: 100%; height: 120px; padding: 15px; border-radius: 12px; border: 1px solid var(--border-color); background: var(--secondary-bg); color: var(--text-color); resize: none; font-family: inherit;"></textarea>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%; margin-bottom: 10px; padding: 15px;">Enviar Mensaje</button>
            <button type="button" class="btn btn-outline" style="width: 100%; padding: 12px;" onclick="closeChatModal()">Cerrar</button>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        initMap(<?php echo $prop['latitud'] ?: 18.4861; ?>, <?php echo $prop['longitud'] ?: -69.9312; ?>, 'map', false);
    });

    function openModal() { document.getElementById('appointmentModal').style.display = 'flex'; }
    function closeModal() { document.getElementById('appointmentModal').style.display = 'none'; }
    
    function openChatModal() { document.getElementById('chatModal').style.display = 'flex'; }
    function closeChatModal() { document.getElementById('chatModal').style.display = 'none'; }

    function toggleReviewEdit(reviewId) {
        const el = document.getElementById('edit-review-' + reviewId);
        if (el) {
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }
    }

    document.getElementById('quick-chat-form')?.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        fetch('chat_handler.php?action=send', {
            method: 'POST',
            body: formData
        }).then(res => res.json())
          .then(data => {
              if (data.success) {
                  if (typeof showNotification === 'function') {
                      showNotification('¡Mensaje enviado con éxito!', 'success');
                  } else {
                      alert('¡Mensaje enviado con éxito!');
                  }
                  closeChatModal();
                  setTimeout(() => {
                      location.href = 'messages.php';
                  }, 800);
              } else {
                  if (typeof showNotification === 'function') {
                      showNotification('Error: ' + data.message, 'error');
                  } else {
                      alert('Error: ' + data.message);
                  }
              }
          });
    });
    function reportPost() {
        const motivo = prompt("¿Por qué deseas reportar esta publicación?");
        if (motivo) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'actions.php';
            form.innerHTML = `
                <input type="hidden" name="action" value="report">
                <input type="hidden" name="property_id" value="<?php echo $id; ?>">
                <input type="hidden" name="motivo" value="${motivo}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
</script>

</body>
</html>
