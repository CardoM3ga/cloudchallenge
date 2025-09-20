<?php
session_start();
require 'conexao.php'; // importa o $pdo

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Pega os dados do formulário
    $nome = $_POST['usuario'];
    $email = $_POST['email'];
    $data_nasc = $_POST['data_nasc'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm-password'];

    // Verifica se as senhas coincidem
    if ($password !== $confirmPassword) {
        echo "As senhas não coincidem!";
        exit;
    }

    // Verifica se o email já está cadastrado
    $stmt = $pdo->prepare("SELECT id_usuario FROM Usuarios_TBL WHERE email = :email LIMIT 1");
    $stmt->execute(['email' => $email]);
    if ($stmt->fetch()) {
        die("Este email já está cadastrado!");
    }

    // Criptografa senha
    $senhaHash = password_hash($password, PASSWORD_DEFAULT);

    // Pegar a data de criação da conta
    $dataCriacao = date('Y-m-d H:i:s'); // formato padrão MySQL

    // Valor default para avatar
    $avatar = './assets/images/avatares/27.png';

    // Insere no banco de dados
    $stmt = $pdo->prepare("INSERT INTO Usuarios_TBL (nome, email, senha, data_nasc, data_criacao, experiencia, nivel, pontos, score, avatar_url) VALUES (:nome, :email, :senha, :data_nasc, :data_criacao , :experiencia, :nivel, :pontos, :score, :avatar_url)");
    $stmt->execute([
        'nome' => $nome,
        'email' => $email,
        'senha' => $senhaHash,
        'data_nasc' => $data_nasc,
        'data_criacao' => $dataCriacao,
        'experiencia' => 0,
        'nivel'       => 1,
        'pontos'      => 0,
        'score'       => 0,
        'avatar_url'  => $avatar
    ]);

    // Pega ID do usuário recém-cadastrado
    $id = $pdo->lastInsertId();

    // Cria sessão
    $_SESSION['usuario'] = [
        'id' => $id,
        'nome' => $nome,
        'email' => $email,
        'data_nasc' => $data_nasc,
        'avatar_url' => $avatar
    ];

    // Redireciona para página inicial
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="manifest" href="/manifest.json">
  <title>Cadastrar - CloudChallenge</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="./css/reset.css">
  <link rel="stylesheet" type="text/css" href="./css/subscribe.css">
  <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <div class="content">


        <div class="left-side">
            <!-- Parte logo e Cadastre-se -->
            <div class="login-logo">
                <div class="logo">
                    <img src="./assets/images/logo.png" alt="CloudChallenge Logo">
                </div>
                <p class="subscribe">Já tem uma conta? <a href="login.php">Entrar</a></p>
            </div>
            <!-- Fim Parte logo e Cadastre-se -->
        </div>

        <div class="right-side">
            <!-- Conteúdo do Subscribe -->
            <div class="login-content">
                <h1 class="login-title">
                    CloudChallenge
                </h1>
                <p class="login-subtitle">
                    Faça login para continuar
                </p>
                <div class="login-form">
                    <form action="subscribe.php" method="post">
                        <div class="input-group">
                            <label for="email">Usuário</label>
                            <input type="text" id="usuario" name="usuario" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <div class="input-group">
                            <label for="data_nasc">Data de Nascimento</label>
                            <input type="date" id="data_nasc" name="data_nasc" required>
                        </div>
                        <div class="input-group">
                            <label for="password">Senha</label>
                            <input type="password" id="password" name="password" required>
                        </div>
                        <div class="input-group">
                            <label for="email">Confirma senha:</label>
                            <input type="password" id="confirm-password" name="confirm-password" required>
                        </div>
                        <button onclick="diretcToIndex" type="submit" class="login-button">Cadastrar</button>
                    </form>
                </div>
            </div>
            <!-- Fim Conteúdo do Subscribe -->
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

        // Envia para PHP via POST para criar sessão
        fetch('google_login.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                nome: data.name,
                email: data.email
            })
        })
        .then(res => res.text())
        .then(() => {
            // Redireciona após criar sessão
            window.location.href = "index.php";
        });
    }

    function diretcToIndex() {
      window.location.href = "index.php";
    }
    
    if ("serviceWorker" in navigator) {
        window.addEventListener("load", () => {
            navigator.serviceWorker.register("/CloudChallenge/sw.js")
            .then((reg) => console.log("Service Worker registrado!", reg))
            .catch((err) => console.log("Falha ao registrar SW:", err));
        });
    }
  </script>
</body>
</html>


