<?php

require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

requireLogin();
$usuari = getUser($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['avatar'])) {
    $file = $_FILES['avatar'];
    $userId = $usuari['id']; // asumiendo que tienes el campo 'id' en la tabla usuarios

    // Verificar errores
    if ($file['error'] === UPLOAD_ERR_OK) {
        // Crear directorio si no existe
        $uploadDir = __DIR__ . '/uploads/avatars/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Nombre único
        $extension = $string = ($sub = strstr($file['type'], "/")) ? substr($sub, 1) : $file['type'];
        $fileName = 'avatar_' . $userId . '.' . $extension;
        $filePath = $uploadDir . $fileName;

        // Mover el archivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // Guardar en la base de datos
            $relativePath = '/uploads/avatars/' . $fileName;
            $stmt = $pdo->prepare("UPDATE usuaris SET avatar = ? WHERE id = ?");
            $stmt->execute([$relativePath, $userId]);

            // Actualizar el objeto usuario en memoria
            $usuari['avatar'] = $relativePath;
            $success = "Imagen actualizada correctamente.";
        } else {
            $error = "Error al guardar la imagen en el servidor.";
        }
    } else {
        $error = "Error en la subida del archivo.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php $usuari['nom_usuari'] ?></title>
    
    <link rel="stylesheet" href="./assets/global.style.css">
    <link rel="stylesheet" href="./assets/index.style.css">
</head>
<body>
    <?php include 'header.php'; ?>
    <div class="home">
        <div class="user-identity">
            <img id="avatarImg" width="120px" height="120px" src="<?php echo $usuari['avatar'] ?? './assets/helmet.png' ?>" alt="Avatar del usuario" style="border-radius:50%; cursor: pointer;">
            <h1><?= htmlspecialchars($usuari['nom_usuari']) ?></h1>
        </div>

        <?php if (isset($error)): ?>
            <p style="color:red;"><?= htmlspecialchars($error) ?></p>
        <?php elseif (isset($success)): ?>
            <p style="color:green;"><?= htmlspecialchars($success) ?></p>
        <?php endif; ?>

        <form id="avatarForm" method="POST" enctype="multipart/form-data" style="display:none;">
            <input type="file" name="avatar" id="avatarInput" accept="image/*">
        </form>
        <label for="username" style="text-align: left;">Nombre de usuario</label>
        <input type="text" name="username" id="username" value="<?php echo $usuari['nom_usuari'] ?>" disabled>
        <label for="username" style="text-align: left;">Contraseña</label>
        <input type="password" name="password" id="password" value="<?php echo $usuari['password_hash'] ?>" disabled>
    </div>
    <script>
        document.getElementById('avatarImg').addEventListener('click', () => {
            document.getElementById('avatarInput').click();
        });

        document.getElementById('avatarInput').addEventListener('change', () => {
            const f = document.getElementById('avatarInput').files;
            if (f && f.length) {
                // submit del formulario -> recarga la página y PHP procesa la subida
                document.getElementById('avatarForm').submit();
            }
        });
    </script>
</body>
</html>