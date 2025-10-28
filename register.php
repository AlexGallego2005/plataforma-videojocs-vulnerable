<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom_usuari = trim($_POST['nom_usuari'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $nom_complet = trim($_POST['nom_complet'] ?? '');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password_confirm'] ?? '';

    if (!$nom_usuari || !$email || !$pass) $errors[] = 'Omple tots els camps obligatoris.';
    if ($pass !== $pass2) $errors[] = 'Les contrasenyes no coincideixen.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email invàlid.';

    if (empty($errors)) {
        // Comprovar duplicats
        $stmt = $pdo->prepare("SELECT id FROM usuaris WHERE nom_usuari = :u OR email = :e LIMIT 1");
        $stmt->execute(['u'=>$nom_usuari,'e'=>$email]);
        if ($stmt->fetch()) {
            $errors[] = "Nom d'usuari o email ja registrat.";
        } else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $ins = $pdo->prepare("INSERT INTO usuaris (nom_usuari,email,password_hash,nom_complet) VALUES (:u,:e,:p,:n)");
            $ins->execute(['u'=>$nom_usuari,'e'=>$email,'p'=>$hash,'n'=>$nom_complet]);
            $_SESSION['usuari_id'] = $pdo->lastInsertId();
            header('Location: /games.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registre - Spartanos</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 50%, #7e22ce 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 480px;
            padding: 40px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .identity {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 32px;
        }

        .identity img {
            width: 80px;
            height: 80px;
            margin-bottom: 16px;
            filter: drop-shadow(0 4px 8px rgba(0, 0, 0, 0.1));
        }

        .identity h1 {
            font-size: 28px;
            color: #1e293b;
            font-weight: 700;
            letter-spacing: -0.5px;
        }

        h2 {
            font-size: 24px;
            color: #334155;
            font-weight: 600;
            margin-bottom: 24px;
            text-align: center;
        }

        .error-messages {
            margin-bottom: 20px;
        }

        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 8px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-message::before {
            content: "⚠";
            font-size: 16px;
            flex-shrink: 0;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .input-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        label {
            font-size: 14px;
            font-weight: 500;
            color: #475569;
        }

        input {
            width: 100%;
            padding: 12px 16px;
            font-size: 15px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            transition: all 0.2s;
            outline: none;
            font-family: inherit;
        }

        input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        input::placeholder {
            color: #94a3b8;
        }

        .optional-label {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 400;
            margin-left: 4px;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 8px;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        button:active {
            transform: translateY(0);
        }

        .footer-link {
            text-align: center;
            margin-top: 24px;
            font-size: 14px;
            color: #64748b;
        }

        .footer-link a {
            color: #3b82f6;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }

        .footer-link a:hover {
            color: #2563eb;
            text-decoration: underline;
        }

        @media (max-width: 480px) {
            .register-container {
                padding: 32px 24px;
            }

            .identity h1 {
                font-size: 24px;
            }

            h2 {
                font-size: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="identity">
            <img src="./assets/helmet.png" alt="Spartanos">
            <h1>Spartanos</h1>
        </div>
        
        <h2>Crear un compte</h2>
        
        <?php if(!empty($errors)): ?>
            <div class="error-messages">
                <?php foreach($errors as $e): ?>
                    <div class="error-message"><?php echo htmlspecialchars($e); ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <div class="input-group">
                <input name="nom_usuari" type="text" placeholder="Nom d'usuari" required autocomplete="username" value="<?php echo htmlspecialchars($_POST['nom_usuari'] ?? ''); ?>">
            </div>
            
            <div class="input-group">
                <input name="email" type="email" placeholder="Correu electrònic" required autocomplete="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
            </div>
            
            <div class="input-group">
                <input name="nom_complet" type="text" placeholder="Nom complet (opcional)" autocomplete="name" value="<?php echo htmlspecialchars($_POST['nom_complet'] ?? ''); ?>">
            </div>
            
            <div class="input-group">
                <input name="password" type="password" placeholder="Contrasenya" required autocomplete="new-password">
            </div>
            
            <div class="input-group">
                <input name="password_confirm" type="password" placeholder="Repeteix contrasenya" required autocomplete="new-password">
            </div>
            
            <button type="submit">Crear compte</button>
        </form>
        
        <div class="footer-link">
            Ja tens compte? <a href="/login.php">Inicia sessió</a>
        </div>
    </div>
</body>
</html>