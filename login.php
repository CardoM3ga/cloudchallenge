<?php
session_start();
require_once 'conexao.php';

// Login tradicional
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'], $_POST['password'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM Usuarios_TBL WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($password, $usuario['senha'])) {
        $_SESSION['usuario'] = [
            'id' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'google_email' => $usuario['google_email'] ?? null
        ];
        header('Location: index.php');
        exit();
    } else {
        echo "<script>alert('Email ou senha inválidos.'); window.location.href='login.php';</script>";
        exit();
    }
}

// Login via Google
$input = file_get_contents('php://input');
if ($input && strpos($input, 'google_email') !== false) {
    $data = json_decode($input, true);
    $email = $data['google_email'] ?? '';

    header('Content-Type: application/json');

    if (!$email) {
        echo json_encode(['status' => 'erro', 'message' => 'Email não fornecido']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM Usuarios_TBL WHERE google_email = :google_email LIMIT 1");
    $stmt->execute(['google_email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION['usuario'] = [
            'id' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'google_email' => $usuario['google_email']
        ];
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'erro', 'message' => 'Conta do Google não vinculada!']);
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#000000">
<title>Login - CloudChallenge</title>
<link rel="icon" type="image/png" href="./assets/icons/LogoDDM.png">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="./css/reset.css">
<link rel="stylesheet" href="./css/menu.css">
<link rel="stylesheet" href="./css/login.css">
<script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
<div class="content">
        <!-- LADO ESQUERDO (logo e texto explicativo) -->
        <div class="left-side">
            <h1>Bem-vindo ao QuizTime</h1>
            <p>Teste seus conhecimentos e desafie seus amigos!</p>
        </div>

        <!-- LADO DIREITO (formulário de login) -->
        <div class="right-side">
            <div class="login-content">
                <h2 class="login-title">Login</h2>
                <p class="login-subtitle">Entre com sua conta</p>
                
                <form class="login-form" method="POST" action="processa_login.php">
                    <div class="input-group">
                        <label for="email">Email</label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="input-group">
                        <label for="senha">Senha</label>
                        <input type="password" name="senha" required>
                    </div>
                    
                    <button type="submit" class="login-button">Entrar</button>
                </form>

                <!-- Botão do Google -->
                <button class="login-google">Login com Google</button>
                
            </div>
            <div class="subscribe">
                    <p>Não tem uma conta? <a href="register.php">Cadastrar-se</a></p>
                </div>
        </div>
    </div>

<script>
function parseJwt(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
        atob(base64)
        .split('')
        .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
        .join('')
    );
    return JSON.parse(jsonPayload);
}

function handleCredentialResponse(response) {
    const data = parseJwt(response.credential);

    fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ google_email: data.email })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === "ok") {
            window.location.href = "index.php";
        } else {
            alert(res.message);
        }
    });
}

function parseJwt(token) {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(
        atob(base64)
        .split('')
        .map(c => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
        .join('')
    );
    return JSON.parse(jsonPayload);
}

function handleCredentialResponse(response) {
    const data = parseJwt(response.credential);

    fetch("login.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ google_email: data.email })
    })
    .then(res => res.json())
    .then(res => {
        if (res.status === "ok") {
            window.location.href = "index.php"; // redireciona se existir
        } else {
            alert(res.message); // mostra erro se não existir
        }
    })
    .catch(() => alert("Erro ao logar com Google."));
}


    if ("serviceWorker" in navigator) {
        window.addEventListener("load", () => {
            navigator.serviceWorker.register("/CloudChallenge/sw.js")
            .then((reg) => console.log("Service Worker registrado!", reg))
            .catch((err) => console.log("Falha ao registrar SW:", err));
        });
    }

</script>

<?php
// Tratamento do login via Google
if ($_SERVER['REQUEST_METHOD'] === 'POST' && strpos(file_get_contents('php://input'), 'google_email') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    $email = $data['google_email'] ?? '';

    header('Content-Type: application/json');

    if (!$email) {
        echo json_encode(['status' => 'erro', 'message' => 'Email não fornecido']);
        exit;
    }

    $stmt = $pdo->prepare("SELECT * FROM Usuarios_TBL WHERE google_email = :google_email LIMIT 1");
    $stmt->execute(['google_email' => $email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $_SESSION['usuario'] = [
            'id' => $usuario['id_usuario'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'google_email' => $usuario['google_email']
        ];
        echo json_encode(['status' => 'ok']);
    } else {
        echo json_encode(['status' => 'erro', 'message' => 'Conta do Google não vinculada!']);
    }
    exit;
}
?>



</body>

</html>

