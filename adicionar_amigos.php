<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_logado = $_SESSION['usuario']['id'];

// Enviar convite
if (isset($_POST['enviar_convite'])) {
    $id_destino = $_POST['id_destino'];

    $stmt = $pdo->prepare("INSERT IGNORE INTO Amizades_TBL (id_usuario1, id_usuario2, status) VALUES (?, ?, 'pendente')");
    $stmt->execute([$id_logado, $id_destino]);
}

// Aceitar convite
if (isset($_POST['aceitar_convite'])) {
    $id_origem = $_POST['id_origem'];

    $stmt = $pdo->prepare("UPDATE Amizades_TBL SET status = 'aceito' WHERE id_usuario1 = ? AND id_usuario2 = ?");
    $stmt->execute([$id_origem, $id_logado]);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="./css/reset.css">
<link rel="stylesheet" type="text/css" href="./css/menu.css">
<link rel="stylesheet" type="text/css" href="./css/adicionar_amigos.css">
</head>
<body>
<div class="main-container">
    <?php include_once 'menu_lateral.php'; ?>

    <main class="content">
        <div class="card-container">
            <h2>Adicionar Amigos</h2>

            <!-- Pesquisa de usuários -->
            <form method="POST" action="" class="search-form">
                <input type="text" name="search" placeholder="Pesquisar usuários..." required>
                <button type="submit" name="buscar">Buscar</button>
            </form>

            <div class="resultados">
                <?php
                if (isset($_POST['buscar'])) {
                    $nome = $_POST['search'];

                   $stmt = $pdo->prepare("SELECT id_usuario, nome AS username, avatar_url, nivel 
                    FROM Usuarios_TBL 
                    WHERE nome LIKE ? AND id_usuario != ?");
                    $stmt->execute(["%$nome%", $id_logado]);
                    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);


                   foreach ($usuarios as $u) {
                    echo "
                    <div class='user-row'>
                        <div class='user-info'>
                            <img src='{$u['avatar_url']}' alt='Avatar' class='avatar'>
                            <div class='user-text'>
                                <span class='username'>{$u['username']}</span>
                                <span class='nivel'>Nível {$u['nivel']}</span>
                            </div>
                        </div>
                        <form method='POST' style='display:inline'>
                            <input type='hidden' name='id_destino' value='{$u['id_usuario']}'>
                            <button type='submit' name='enviar_convite'>Adicionar</button>
                        </form>
                    </div>";
                }

                    } else {
                        echo "<p>Nenhum usuário encontrado.</p>";
                    }
                
                ?>
            </div>

            <hr>

            <h3>Convites Pendentes</h3>
            <div class="pendentes">
                <?php
                $stmt = $pdo->prepare("SELECT a.id_usuario1, u.nome AS username, u.avatar_url, u.nivel
                    FROM Amizades_TBL a 
                    JOIN Usuarios_TBL u ON a.id_usuario1 = u.id_usuario 
                    WHERE a.id_usuario2 = ? AND a.status = 'pendente'");
                    $stmt->execute([$id_logado]);
                    $pendentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if ($pendentes) {
                    foreach ($pendentes as $p) {
                        echo "
                        <div class='user-row'>
                            <div class='user-info'>
                                <img src='{$p['avatar_url']}' alt='Avatar' class='avatar'>
                                <div class='user-text'>
                                    <span class='username'>{$p['username']}</span>
                                    <span class='nivel'>Nível {$p['nivel']}</span>
                                </div>
                            </div>
                            <form method='POST' style='display:inline'>
                                <input type='hidden' name='id_origem' value='{$p['id_usuario1']}'>
                                <button type='submit' name='aceitar_convite'>Aceitar</button>
                            </form>
                        </div>";
}
                } else {
                    echo "<p>Nenhum convite pendente.</p>";
                }
                ?>
            </div>
        </div>
    </main>
</div>
</body>

</html>