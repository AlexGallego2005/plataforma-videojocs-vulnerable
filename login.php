<?php
require_once __DIR__ . '/secret/db.php';
require_once __DIR__ . '/secret/auth.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $u = trim($_POST['user'] ?? '');
    $p = $_POST['password'] ?? '';
    if (loginUser($pdo, $u, $p)) {
        header('Location: /games.php');
        exit;
    } else {
        $error = 'Credencials incorrectes.';
    }
}
?>
<!doctype html>
<html lang="ca">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inici de sessió - Spartanos</title>
    <link rel="icon" type="image/jpg" href="assets/helmet.png"/>
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

        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 420px;
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

        .error-message {
            background: #fee2e2;
            border: 1px solid #fecaca;
            color: #991b1b;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .error-message::before {
            content: "⚠";
            font-size: 16px;
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
            .login-container {
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
    <div class="login-container">
        <div class="identity">
            <img src="./assets/helmet.png" alt="Spartanos">
            <h1>Spartanos</h1>
        </div>
        
        <h2>Inicia sessió</h2>
        
        <?php if($error): ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="post">
            <div class="input-group">
                <input name="user" type="text" placeholder="Usuari o email" required autocomplete="username">
            </div>
            
            <div class="input-group">
                <input name="password" type="password" placeholder="Contrasenya" required autocomplete="current-password">
            </div>
            
            <button type="submit">Entrar</button>
        </form>
        
        <div class="footer-link">
            No tens compte? <a href="/register.php">Registra't ara</a>
        </div>
    </div>
</body>
</html>