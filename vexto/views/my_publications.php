<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';

$user_id = $_SESSION['user_id'];

// Procesar eliminación de publicación
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $property_id = $_POST['property_id'];
    
    try {
        $pdo->beginTransaction();
        
        // Verificar que la propiedad pertenece al usuario
        $stmt = $pdo->prepare("SELECT id FROM properties WHERE id = ? AND user_id = ?");
        $stmt->execute([$property_id, $user_id]);
        if ($stmt->fetch()) {
            // Eliminar la propiedad
            $stmt = $pdo->prepare("DELETE FROM properties WHERE id = ? AND user_id = ?");
            $stmt->execute([$property_id, $user_id]);
            
            // Decrementar contador de propiedades
            $stmt = $pdo->prepare("UPDATE users SET propiedades_publicadas = propiedades_publicadas - 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            
            $pdo->commit();
            header("Location: my_publications.php?deleted=1");
        }
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Error al eliminar: " . $e->getMessage());
    }
}

// Obtener todas las publicaciones del usuario
$stmt = $pdo->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$properties = $stmt->fetchAll();

// Obtener datos del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>

<div class="main-container" style="max-width: 1200px; margin: 40px auto; flex-direction: column;">
    <div style="width: 100%;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
            <h1 style="font-size: 2rem; font-weight: 800;">Mis Publicaciones</h1>
            <a href="publish.php" class="btn btn-primary" style="padding: 12px 25px;">+ Nueva Publicación</a>
        </div>

        <?php if (isset($_GET['deleted'])): ?>
            <div style="background: #c6f6d5; border: 1px solid #9ae6b4; padding: 15px; border-radius: 8px; color: #22543d; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-check-circle"></i>
                <span>Publicación eliminada correctamente.</span>
            </div>
        <?php endif; ?>

        <?php if (empty($properties)): ?>
            <div style="text-align: center; padding: 60px 20px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;">
                <i class="fas fa-inbox" style="font-size: 3rem; color: #ccc; margin-bottom: 20px; display: block;"></i>
                <h2 style="margin-bottom: 10px; color: #666;">No tienes publicaciones aún</h2>
                <p style="color: #999; margin-bottom: 20px;">Comienza a publicar propiedades para que otros usuarios las vean.</p>
                <a href="publish.php" class="btn btn-primary">Crear Primera Publicación</a>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 25px;">
                <?php foreach ($properties as $prop): ?>
                    <div class="property-card" style="position: relative; overflow: visible;">
                        <div class="property-img-container" style="position: relative;">
                            <?php
                                $imageSrc = getPropertyImageUrl($prop['imagen_url']);
                                if (!empty($prop['imagen'])) {
                                    $imageSrc = 'data:' . htmlspecialchars($prop['imagen_tipo']) . ';base64,' . base64_encode($prop['imagen']);
                                }
                            ?>
                            <img src="<?php echo htmlspecialchars($imageSrc); ?>" alt="<?php echo htmlspecialchars($prop['titulo']); ?>" class="property-img" style="width: 100%; height: 220px; object-fit: cover;">
                            <span class="op-badge"><?php echo ucfirst($prop['tipo_operacion']); ?></span>
                            <span style="position: absolute; top: 15px; right: 15px; background: rgba(0,0,0,0.7); color: #fff; padding: 6px 12px; font-size: 0.75rem; border-radius: 4px; font-weight: 600;">
                                <?php echo ucfirst($prop['estado']); ?>
                            </span>
                        </div>
                        
                        <div class="property-info">
                            <div class="property-price" style="color: var(--accent-color);">$<?php echo number_format($prop['precio'], 2); ?></div>
                            <div class="property-title"><?php echo htmlspecialchars($prop['titulo']); ?></div>
                            
                            <div class="property-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars(substr($prop['ubicacion'], 0, 30)); ?>...</span>
                                <span><i class="fas fa-eye"></i> <?php echo $prop['vistas']; ?> vistas</span>
                            </div>

                            <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid var(--border-color);">
                                <div style="text-align: center;">
                                    <i class="fas fa-door-open" style="color: #666;"></i>
                                    <div style="font-size: 0.8rem; color: #999;"><?php echo $prop['habitaciones']; ?> hab</div>
                                </div>
                                <div style="text-align: center;">
                                    <i class="fas fa-bath" style="color: #666;"></i>
                                    <div style="font-size: 0.8rem; color: #999;"><?php echo $prop['banos']; ?> baños</div>
                                </div>
                                <div style="text-align: center;">
                                    <i class="fas fa-ruler-combined" style="color: #666;"></i>
                                    <div style="font-size: 0.8rem; color: #999;"><?php echo $prop['area_m2']; ?> m²</div>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                                <a href="publish.php?edit=<?php echo $prop['id']; ?>" class="btn btn-primary" style="text-align: center; padding: 10px; font-size: 0.9rem;">
                                    <i class="fas fa-edit"></i> Editar
                                </a>
                                <form action="my_publications.php" method="POST" style="display: contents;">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="property_id" value="<?php echo $prop['id']; ?>">
                                    <button type="submit" class="btn btn-outline" style="padding: 10px; font-size: 0.9rem;" onclick="return confirm('¿Estás seguro de que deseas eliminar esta publicación?');">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="margin-top: 40px; padding: 20px; background: var(--card-bg); border: 1px solid var(--border-color); border-radius: 12px;">
                <h3 style="margin-bottom: 15px;">Resumen de Publicaciones</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div>
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Total Publicadas</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-color);"><?php echo count($properties); ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Límite de Publicaciones</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-color);"><?php echo $user['max_propiedades']; ?></div>
                    </div>
                    <div>
                        <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Vistas Totales</div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--accent-color);">
                            <?php 
                            $total_views = 0;
                            foreach ($properties as $prop) {
                                $total_views += $prop['vistas'];
                            }
                            echo $total_views;
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
    .btn-outline {
        background: transparent;
        border: 1px solid var(--accent-color);
        color: var(--text-color);
        transition: all 0.3s ease;
    }
    
    .btn-outline:hover {
        background: var(--accent-color);
        color: var(--accent-text);
    }
</style>

</body>
</html>
