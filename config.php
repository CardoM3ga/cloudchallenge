<?php
session_start();
require_once 'conexao.php';

// garante que só entra logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];

// Busca dados do usuário (para mostrar email e Google vinculado)
$stmt = $pdo->prepare("SELECT nome, email, google_email FROM Usuarios_TBL WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado!";
    exit;
}

// --------- FUNÇÕES DE ALTERAÇÃO ----------

// Alterar senha
if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
    $novaSenha = $_POST['senha'] ?? '';
    $hash = password_hash($novaSenha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET senha = ? WHERE id_usuario = ?");
    if ($stmt->execute([$hash, $id_usuario])) {
        echo json_encode(['success' => true, 'message' => 'Senha alterada com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar senha.']);
    }
    exit;
}

// Alterar email
if (isset($_POST['acao']) && $_POST['acao'] === 'alterar_email') {
    $novoEmail = $_POST['email'] ?? '';
    if (!filter_var($novoEmail, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Email inválido!']);
        exit;
    }
    $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET email = ? WHERE id_usuario = ?");
    if ($stmt->execute([$novoEmail, $id_usuario])) {
        echo json_encode(['success' => true, 'message' => 'Email alterado com sucesso!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao alterar email.']);
    }
    exit;
}

// Vincular Google
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['google_token'])) {
    $tokenParts = explode('.', $_POST['google_token']);
    if (count($tokenParts) === 3) {
        $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);
        if ($payload && isset($payload['email'])) {
            $googleEmail = $payload['email'];
            $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET google_email = :google_email WHERE id_usuario = :id");
            $success = $stmt->execute([
                'google_email' => $googleEmail,
                'id' => $id_usuario
            ]);
            $_SESSION['usuario']['google_email'] = $googleEmail;
            $_SESSION['google_status'] = $success ? 'ok' : 'erro';
            header("Location: config.php");
            exit;
        }
    }
    $_SESSION['google_status'] = 'erro';
    header("Location: config.php");
    exit;
}

// Desvincular Google
if (isset($_POST['acao']) && $_POST['acao'] === 'desvincular_google') {
    $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET google_email = NULL WHERE id_usuario = ?");
    $success = $stmt->execute([$id_usuario]);
    $_SESSION['usuario']['google_email'] = null;
    $_SESSION['google_status'] = $success ? 'desvinculado' : 'erro';
    header("Location: config.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/config.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <!-- <style>
     Garantir layout alinhado
        .content {
            display: flex;
            flex-direction: row;
            min-height: 100vh;
        }

        .content_configuracoes {
            flex: 1;
            background-color: #f1ebcf;
            border-radius: 20px;
            padding: 30px;
            margin: 25px;
            max-width: 950px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.12);
        }

        .title_config {
            font-size: 24px;
            font-weight: 600;
            margin-left: 10px;
            vertical-align: middle;
            color: #0e28b5;
        }

        .content_configuracoes i.fa-gear {
            font-size: 28px;
            color: #0e28b5;
            vertical-align: middle;
        }
    </style> -->
</head>
<body>
<div class="container">
  <?php include_once 'menu_lateral.php'; ?>
    <div class="content">
      

        <div class="content_configuracoes">
            <i class="fa-solid fa-gear"></i>
            <span class="title_config">Configurações</span>

            <!-- Seção Conta -->
            <div class="section">
                <h2>Conta</h2>
                <div class="options">
                    <!-- Email atual -->
                    <div class="option-row">
                        <p><strong>Email atual:</strong> <?= htmlspecialchars($usuario['email']) ?></p>
                        <button class="config-btn" onclick="alterarEmail()">Alterar email</button>
                    </div>

                    <div class="option-row">
                        <button class="config-btn" onclick="alterarSenha()">Alterar senha</button>
                    </div>

                    <!-- Conta Google -->
                    <?php if (empty($usuario['google_email'])): ?>
                        <div class="option-row">
                            <p>Nenhuma conta Google vinculada.</p>
                            <div id="g_id_onload"
                                data-client_id="282009522215-ik4r5bkdb6ao7hed6q7am0mk553bnm7j.apps.googleusercontent.com"
                                data-callback="handleAddGoogle"
                                data-auto_prompt="false">
                            </div>
                            <div class="g_id_signin" data-type="standard"></div>
                        </div>
                    <?php else: ?>
                        <div class="option-row">
                            <p>Conta Google vinculada: <strong><?= htmlspecialchars($usuario['google_email']) ?></strong></p>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="acao" value="desvincular_google">
                                <button class="config-btn" type="submit">Desvincular conta Google</button>
                            </form>
                        </div>
                        <div class="option-row">       <!-- Logout -->
                            <form action="logout.php" method="POST" style="margin-top: 10px;">
                                <button type="submit"  class="config-btn sair">
                                    <i class="fa-solid fa-sign-out-alt sair"></i> Sair
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Seção Acessibilidade -->
            <div class="section">
                <h2>Acessibilidade</h2>
                <div class="options">
                    <div class="btn-text">
                        <button class="config-btn" id="aumentarTexto">Aumentar texto</button>
                        <button class="config-btn" id="diminuirTexto">Diminuir texto</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
 </div>

    <?php
    if (isset($_SESSION['google_status'])) {
        if ($_SESSION['google_status'] === 'ok') {
            echo "<script>alert('Conta Google conectada com sucesso!');</script>";
        } elseif ($_SESSION['google_status'] === 'desvinculado') {
            echo "<script>alert('Conta Google desvinculada com sucesso!');</script>";
        } else {
            echo "<script>alert('Erro ao processar a ação do Google.');</script>";
        }
        unset($_SESSION['google_status']);
    }
    ?>

    <script>
    function handleAddGoogle(response) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.style.display = 'none';

        const tokenInput = document.createElement('input');
        tokenInput.name = 'google_token';
        tokenInput.value = response.credential;
        form.appendChild(tokenInput);

        document.body.appendChild(form);
        form.submit();
    }

    function alterarSenha() {
        const novaSenha = prompt("Digite a nova senha:");
        if (!novaSenha) return;

        const confirmaSenha = prompt("Confirme a nova senha:");
        if (novaSenha !== confirmaSenha) {
            alert("As senhas não conferem!");
            return;
        }

        fetch("config.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `acao=alterar_senha&senha=${encodeURIComponent(novaSenha)}`
        })
        .then(res => res.json())
        .then(data => alert(data.message))
        .catch(() => alert("Erro ao alterar senha."));
    }

    function alterarEmail() {
        const novoEmail = prompt("Digite o novo email:");
        if (!novoEmail) return;

        const confirmaEmail = prompt("Confirme o novo email:");
        if (novoEmail !== confirmaEmail) {
            alert("Os emails não conferem!");
            return;
        }

        fetch("config.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `acao=alterar_email&email=${encodeURIComponent(novoEmail)}`
        })
        .then(res => res.json())
        .then(data => alert(data.message))
        .catch(() => alert("Erro ao alterar email."));
    }
    </script>

    <script src="./js/config_global.js"></script>
</body>
</html>