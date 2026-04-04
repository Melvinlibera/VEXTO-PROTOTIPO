<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/index.php");
    exit();
}
require_once dirname(__DIR__) . '/config/db.php';
include dirname(__DIR__) . '/includes/header.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_data = $stmt->fetch();

$errors = [];
$updated = false;
$formData = [
    'nombre' => $user_data['nombre'],
    'apellido' => $user_data['apellido'],
    'telefono' => $user_data['telefono'],
    'bio' => $user_data['bio'],
    'email' => $user_data['email'],
    'cedula' => $user_data['cedula'],
    'rnc' => $user_data['rnc'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $email = trim($_POST['email'] ?? $user_data['email']);
    $cedula = trim($_POST['cedula'] ?? '');
    $rnc = trim($_POST['rnc'] ?? '');

    $formData = [
        'nombre' => $nombre,
        'apellido' => $apellido,
        'telefono' => $telefono,
        'bio' => $bio,
        'email' => $email,
        'cedula' => $cedula,
        'rnc' => $rnc,
    ];

    if ($nombre === '') {
        $errors[] = 'El nombre es obligatorio.';
    }
    if ($apellido === '') {
        $errors[] = 'El apellido es obligatorio.';
    }
    if ($telefono === '') {
        $errors[] = 'El teléfono de contacto es obligatorio.';
    }

    $emailEditable = intval($user_data['email_change_count'] ?? 0) === 0;
    if ($emailEditable && $email !== $user_data['email']) {
        if (!isValidEmail($email)) {
            $errors[] = 'El correo electrónico no es válido.';
        } else {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
            $stmt->execute([$email, $user_id]);
            if ($stmt->fetch()) {
                $errors[] = 'Este correo electrónico ya está registrado.';
            }
        }
    } elseif (!$emailEditable) {
        $email = $user_data['email'];
    }

    if (empty($errors)) {
        $updateFields = ['nombre', 'apellido', 'telefono', 'bio'];
        $params = [$nombre, $apellido, $telefono, $bio];

        if ($password !== '') {
            $updateFields[] = 'password';
            $params[] = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($emailEditable && $email !== $user_data['email']) {
            $updateFields[] = 'email';
            $params[] = $email;
            $updateFields[] = 'email_change_count';
            $params[] = 1;
        }

        if (($user_data['cedula'] === null || $user_data['cedula'] === '') && $cedula !== '') {
            $updateFields[] = 'cedula';
            $params[] = $cedula;
        }

        if ($user_data['tipo_usuario'] === 'compania' && ($user_data['rnc'] === null || $user_data['rnc'] === '') && $rnc !== '') {
            $updateFields[] = 'rnc';
            $params[] = $rnc;
        }

        if (!empty($updateFields)) {
            $sql = 'UPDATE users SET ' . implode(', ', array_map(fn($field) => "$field = ?", $updateFields)) . ' WHERE id = ?';
            $params[] = $user_id;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            $_SESSION['nombre'] = $nombre;
            $updated = true;
            header("Location: settings.php?updated=1");
            exit();
        }
    }
}
?>

<div class="main-container" style="flex-direction: column; max-width: 800px; margin: 40px auto;">
    <h1 style="margin-bottom: 30px; font-size: 2.5rem; font-weight: 900;">Configuración de Cuenta</h1>
    
    <div class="filter-card" style="padding: 40px;">
        <form action="settings.php" method="POST">
            <input type="hidden" name="update_profile" value="1">

            <?php if (!empty($errors)): ?>
                <div style="margin-bottom: 25px; padding: 18px; border-radius: 10px; background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.3); color: #b91c1c;">
                    <strong>Por favor corrige lo siguiente:</strong>
                    <ul style="margin: 10px 0 0; padding-left: 20px;">
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="filter-group" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div>
                    <label>Nombre</label>
                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($formData['nombre']); ?>" required>
                </div>
                <div>
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="<?php echo htmlspecialchars($formData['apellido']); ?>" required>
                </div>
            </div>

            <div class="filter-group">
                <label>Correo Electrónico</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($formData['email']); ?>" <?php echo intval($user_data['email_change_count'] ?? 0) > 0 ? 'readonly' : ''; ?> required>
                <small style="display: block; margin-top: 8px; color: var(--muted-text);">
                    <?php if (intval($user_data['email_change_count'] ?? 0) > 0): ?>
                        Este correo ya no puede modificarse nuevamente.
                    <?php else: ?>
                        Solo puedes cambiar este correo una vez.
                    <?php endif; ?>
                </small>
            </div>

            <div class="filter-group">
                <label>Género</label>
                <input type="text" value="<?php echo htmlspecialchars($user_data['genero']); ?>" readonly>
                <small style="display: block; margin-top: 8px; color: var(--muted-text);">El género no es modificable desde esta pantalla.</small>
            </div>

            <div class="filter-group">
                <label>Cédula</label>
                <?php if (empty($user_data['cedula'])): ?>
                    <input type="text" name="cedula" value="<?php echo htmlspecialchars($formData['cedula']); ?>" placeholder="Ingresa tu cédula">
                    <small style="display: block; margin-top: 8px; color: var(--muted-text);">Puedes añadir tu cédula si aún no está registrada. No podrás modificarla después.</small>
                <?php else: ?>
                    <input type="text" value="<?php echo htmlspecialchars($user_data['cedula']); ?>" readonly>
                <?php endif; ?>
            </div>

            <?php if ($user_data['tipo_usuario'] === 'compania'): ?>
                <div class="filter-group">
                    <label>RNC</label>
                    <?php if (empty($user_data['rnc'])): ?>
                        <input type="text" name="rnc" value="<?php echo htmlspecialchars($formData['rnc']); ?>" placeholder="Ingresa tu RNC">
                        <small style="display: block; margin-top: 8px; color: var(--muted-text);">Puedes añadir tu RNC si aún no está registrado. No podrás modificarlo después.</small>
                    <?php else: ?>
                        <input type="text" value="<?php echo htmlspecialchars($user_data['rnc']); ?>" readonly>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="filter-group">
                <label>Teléfono de Contacto</label>
                <input type="text" name="telefono" value="<?php echo htmlspecialchars($formData['telefono']); ?>" required>
            </div>

            <div class="filter-group">
                <label>Biografía / Descripción del Vendedor</label>
                <textarea name="bio" rows="4" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px; background: var(--secondary-bg); color: var(--text-color);"><?php echo htmlspecialchars($formData['bio']); ?></textarea>
            </div>

            <div class="filter-group">
                <label>Nueva Contraseña (dejar en blanco para no cambiar)</label>
                <input type="password" name="password" placeholder="********">
            </div>

            <div style="background: var(--secondary-bg); padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 1px solid var(--border-color);">
                <h3 style="font-size: 0.9rem; text-transform: uppercase; margin-bottom: 10px;">Información de Cuenta</h3>
                <p>Tipo de Usuario: <strong><?php echo strtoupper($user_data['tipo_usuario']); ?></strong></p>
                <p>Cédula / RNC: <strong><?php echo htmlspecialchars($user_data['cedula']); ?></strong></p>
                <p>Límite de Publicaciones: <strong><?php echo $user_data['max_propiedades']; ?></strong></p>
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 15px; font-size: 1.1rem;">Guardar Cambios</button>
        </form>
    </div>
</div>

</body>
</html>
