<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';

// Filtros
$filter_op = $_GET['op'] ?? '';
$filter_type = $_GET['type'] ?? '';
$filter_price_min = (float)($_GET['pmin'] ?? 0);
$filter_price_max = (float)($_GET['pmax'] ?? 999999999);
$filter_loc = $_GET['loc'] ?? '';
$search = $_GET['q'] ?? '';

$query = "SELECT p.*, u.nombre, u.apellido, u.rating, u.tipo_usuario, u.foto_perfil 
          FROM properties p 
          JOIN users u ON p.user_id = u.id 
          WHERE 1=1";
$params = [];

if ($filter_op) { $query .= " AND p.tipo_operacion = ?"; $params[] = $filter_op; }
if ($filter_type) { $query .= " AND p.tipo_propiedad = ?"; $params[] = $filter_type; }
if ($filter_price_min) { $query .= " AND p.precio >= ?"; $params[] = $filter_price_min; }
if ($filter_price_max) { $query .= " AND p.precio <= ?"; $params[] = $filter_price_max; }
if ($filter_loc) { $query .= " AND p.ubicacion LIKE ?"; $params[] = "%$filter_loc%"; }
if ($search) { $query .= " AND (p.titulo LIKE ? OR p.descripcion LIKE ?)"; $params[] = "%$search%"; $params[] = "%$search%"; }

$query .= " ORDER BY p.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute($params);
$properties = $stmt->fetchAll();
?>

<div class="main-container">
    <!-- Sidebar de Filtros -->
    <aside class="sidebar">
        <div class="filter-card">
            <form action="dashboard.php" method="GET">
                <input type="hidden" name="q" value="<?php echo htmlspecialchars($search); ?>">
                
                <div class="filter-group">
                    <h3>Operación</h3>
                    <label><input type="radio" name="op" value="" <?php echo $filter_op == '' ? 'checked' : ''; ?>> Todos</label>
                    <label><input type="radio" name="op" value="venta" <?php echo $filter_op == 'venta' ? 'checked' : ''; ?>> Venta</label>
                    <label><input type="radio" name="op" value="alquiler" <?php echo $filter_op == 'alquiler' ? 'checked' : ''; ?>> Alquiler</label>
                </div>

                <div class="filter-group">
                    <h3>Tipo de Propiedad</h3>
                    <select name="type">
                        <option value="">Todos los tipos</option>
                        <option value="casa" <?php echo $filter_type == 'casa' ? 'selected' : ''; ?>>Casa</option>
                        <option value="apartamento" <?php echo $filter_type == 'apartamento' ? 'selected' : ''; ?>>Apartamento</option>
                        <option value="local" <?php echo $filter_type == 'local' ? 'selected' : ''; ?>>Local Comercial</option>
                        <option value="terreno" <?php echo $filter_type == 'terreno' ? 'selected' : ''; ?>>Terreno</option>
                    </select>
                </div>

                <div class="filter-group">
                    <h3>Ubicación</h3>
                    <input type="text" name="loc" placeholder="Ciudad o zona..." value="<?php echo htmlspecialchars($filter_loc); ?>">
                </div>

                <div class="filter-group">
                    <h3>Rango de Precio</h3>
                    <input type="number" name="pmin" placeholder="Mínimo" value="<?php echo $filter_price_min ?: ''; ?>">
                    <input type="number" name="pmax" placeholder="Máximo" value="<?php echo $filter_price_max == 999999999 ? '' : $filter_price_max; ?>">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">Aplicar Filtros</button>
                <a href="dashboard.php" class="btn btn-outline" style="width: 100%; margin-top: 10px;">Limpiar</a>
            </form>
        </div>
    </aside>

    <!-- Grid de Propiedades -->
    <main class="content-grid">
        <?php if (empty($properties)): ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 50px;">
                <i class="fas fa-search" style="font-size: 3rem; color: #ccc; margin-bottom: 20px;"></i>
                <h2>No encontramos resultados</h2>
                <p>Intenta ajustar los filtros o buscar algo diferente.</p>
            </div>
        <?php else: ?>
            <?php foreach ($properties as $prop): ?>
                <div class="property-card" onclick="location.href='property_details.php?id=<?php echo $prop['id']; ?>'">
                    <div class="property-img-container">
                        <span class="op-badge"><?php echo strtoupper($prop['tipo_operacion']); ?></span>
                        <?php if ($prop['tipo_usuario'] == 'compania'): ?>
                            <span class="user-badge" style="background: #000; color: #fff;"><i class="fas fa-building"></i> PRO</span>
                        <?php endif; ?>
                        <?php
                            $imgSrc = getPropertyImageUrl($prop['imagen_url']);
                            if (!empty($prop['imagen'])) {
                                $imgSrc = 'data:' . htmlspecialchars($prop['imagen_tipo']) . ';base64,' . base64_encode($prop['imagen']);
                            }
                        ?>
                        <img src="<?php echo htmlspecialchars($imgSrc); ?>" class="property-img" alt="Propiedad">
                    </div>
                    <div class="property-info">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <div class="property-price" style="font-size: 1.4rem; font-weight: 900; color: var(--accent-color);">
                                $<?php echo number_format($prop['precio'], 0); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #777; font-weight: 600; text-transform: uppercase;">
                                <?php echo htmlspecialchars($prop['tipo_propiedad']); ?>
                            </div>
                        </div>
                        <div class="property-title" style="font-size: 1rem; font-weight: 700; margin-bottom: 12px; height: 1.2em; overflow: hidden; text-overflow: ellipsis;">
                            <?php echo htmlspecialchars($prop['titulo']); ?>
                        </div>
                        <div class="property-meta" style="display: flex; gap: 12px; color: #666; font-size: 0.8rem; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 12px;">
                            <span><i class="fas fa-bed"></i> <?php echo $prop['habitaciones']; ?></span>
                            <span><i class="fas fa-bath"></i> <?php echo $prop['banos']; ?></span>
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($prop['ubicacion']); ?></span>
                        </div>
                        <div class="seller-mini" style="display: flex; align-items: center; gap: 10px; border-top: none; padding-top: 0; margin-top: 0;">
                            <div class="seller-avatar-mini" style="width: 24px; height: 24px; background: #eee; border-radius: 50%;"></div>
                            <div class="seller-name-mini" style="font-size: 0.8rem; font-weight: 600; flex: 1;">
                                <?php echo htmlspecialchars($prop['nombre']); ?>
                            </div>
                            <div style="font-size: 0.75rem; font-weight: 800; color: var(--accent-color);">
                                <i class="fas fa-star"></i> <?php echo number_format($prop['rating'], 1); ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </main>
</div>

</body>
</html>
