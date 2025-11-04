<?php

require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

requireLogin();
$usuari = getUser($pdo);

// --- Manejo de Peticiones POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // --- 1. Actualización de Avatar ---
    if (isset($_FILES['avatar'])) {
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

    // --- 2. Actualización de Perfil (Usuario/Contraseña) ---
    } elseif (isset($_POST['update_profile'])) {
        
        $userId = $usuari['id'];
        $new_username = trim($_POST['username']);
        $new_password = $_POST['password'];
        $confirm_password = $_POST['password_confirm'];

        $sql_parts = [];
        $params = [];
        $update_triggered = false; // Para saber si hay algo que actualizar

        // A. Validar y preparar nombre de usuario
        if (!empty($new_username) && $new_username !== $usuari['nom_usuari']) {
            // Comprobar si el nuevo nombre de usuario ya existe
            $stmt_check = $pdo->prepare("SELECT id FROM usuaris WHERE nom_usuari = ? AND id != ?");
            $stmt_check->execute([$new_username, $userId]);
            if ($stmt_check->fetch()) {
                $error = "Ese nombre de usuario ya está en uso.";
            } else {
                $sql_parts[] = "nom_usuari = ?";
                $params[] = $new_username;
                $usuari['nom_usuari'] = $new_username; // Actualizar localmente
                $update_triggered = true;
            }
        }

        // B. Validar y preparar contraseña (solo si no hubo error de usuario)
        if (!isset($error) && !empty($new_password)) {
            if ($new_password !== $confirm_password) {
                $error = "Las contraseñas no coinciden.";
            } else {
                // Opcional: Añadir validación de fortaleza de contraseña aquí
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $sql_parts[] = "password_hash = ?";
                $params[] = $hashed_password;
                $update_triggered = true;
            }
        }

        // C. Ejecutar actualización en BD (si hay cambios y no hay errores)
        if ($update_triggered && !isset($error)) {
            $sql = "UPDATE usuaris SET " . implode(', ', $sql_parts) . " WHERE id = ?";
            $params[] = $userId;
            
            $stmt_update = $pdo->prepare($sql);
            if ($stmt_update->execute($params)) {
                $success = "Perfil actualizado correctamente.";
            } else {
                $error = "Error al actualizar el perfil en la base de datos.";
            }
        } elseif (!$update_triggered && !isset($error)) {
            // El usuario hizo clic en "Actualizar" sin cambiar nada
            $success = "No se detectaron cambios para actualizar.";
        }
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
    <style>
        /* Estilos básicos para el nuevo formulario */
        .profile-form {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 400px; /* Ajusta según tu diseño */
            margin-top: 20px;
        }
        .profile-form label {
            text-align: left;
            margin-top: 10px;
        }
        .profile-form input {
            /* Asumiendo que tienes estilos globales para input */
            width: 100%;
            padding: 8px;
            box-sizing: border-box; /* Para que el padding no afecte el width */
        }
        .profile-form button {
            /* Asumiendo que tienes estilos globales para button */
            margin-top: 15px;
            padding: 10px;
        }
    </style>
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

        <form method="POST" action="" class="profile-form">
            <label for="username" style="text-align: left;">Nombre de usuario</label>
            <input type="text" name="username" id="username" value="<?= htmlspecialchars($usuari['nom_usuari']) ?>" required>
            
            <label for="password" style="text-align: left;">Nueva Contraseña</label>
            <input type="password" name="password" id="password" value="" placeholder="Dejar en blanco para no cambiar">

            <label for="password_confirm" style="text-align: left;">Confirmar Nueva Contraseña</label>
            <input type="password" name="password_confirm" id="password_confirm" placeholder="Repetir nueva contraseña">
            
            <button type="submit" name="update_profile">Actualizar Perfil</button>
        </form>
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