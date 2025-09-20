<?php
session_start();
require_once 'conexao.php';

// garante que só entra logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];

// Busca pontos do usuário
$stmt = $pdo->prepare("SELECT id_usuario, nome, score, avatar_url FROM Usuarios_TBL WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->query("
    SELECT id_usuario, nome, avatar_url, score
    FROM Usuarios_TBL
    ORDER BY score DESC
    LIMIT 50
");
$rankingGlobal = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ranking Amigos (inclui o próprio jogador)
$stmt = $pdo->prepare("
    SELECT u.id_usuario, u.nome, u.avatar_url, u.score
    FROM Usuarios_TBL u
    JOIN Amizades_TBL a
      ON (
          (a.id_usuario1 = :id_usuario AND a.id_usuario2 = u.id_usuario)
          OR
          (a.id_usuario2 = :id_usuario AND a.id_usuario1 = u.id_usuario)
      )
    WHERE a.status = 'aceito'

    UNION

    SELECT u.id_usuario, u.nome, u.avatar_url, u.score
    FROM Usuarios_TBL u
    WHERE u.id_usuario = :id_usuario

    ORDER BY score DESC
");
$stmt->execute([':id_usuario' => $id_usuario]);
$rankingAmigos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// garante que o jogador apareça na lista de amigos
$jogadorJaNaLista = array_filter($rankingAmigos, fn($a) => $a['id_usuario'] == $id_usuario);
if (!$jogadorJaNaLista) {
    $rankingAmigos[] = $usuario;
    usort($rankingAmigos, fn($a, $b) => $b['pontos'] <=> $a['pontos']);
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/ranking.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
</head>
<body>
<div class="content">
   <?php include_once 'menu_lateral.php'; ?>

    <div class="main">
        <h1>Ranking</h1>
        <div class="divider_ranking"></div>

        <div class="ranking-buttons">
            <button onclick="mostrarRanking('geral')" class="active">Geral</button>
            <button onclick="mostrarRanking('amigos')">Amigos</button>
        </div>

        <!-- Ranking Geral -->
        <div id="ranking-geral" class="ranking-list">
            <?php foreach ($rankingGlobal as $i => $user): ?>
            <?php
                $classeExtra = '';
                if ($i == 0) $classeExtra = 'top1';
                elseif ($i == 1) $classeExtra = 'top2';
                elseif ($i == 2) $classeExtra = 'top3';
                if ($user['id_usuario'] == $id_usuario) $classeExtra .= ' me';
            ?>
            <div class="ranking-item <?= $classeExtra ?>">
                <span class="pos"><?= $i+1 ?>º</span>
                <img class="avatar" src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar">
                <span class="nome"><?= htmlspecialchars($user['nome']) ?></span>
                <span class="score"><?= $user['score'] ?> Score</span>
            </div>
        <?php endforeach; ?>

        </div>

        <!-- Ranking Amigos -->
        <div id="ranking-amigos" class="ranking-list hidden">
            <?php foreach ($rankingAmigos as $i => $user): ?>
            <?php
                $classeExtra = '';
                if ($i == 0) $classeExtra = 'top1';
                elseif ($i == 1) $classeExtra = 'top2';
                elseif ($i == 2) $classeExtra = 'top3';
                if ($user['id_usuario'] == $id_usuario) $classeExtra .= ' me';
            ?>
            <div class="ranking-item <?= $classeExtra ?>">
                <span class="pos"><?= $i+1 ?>º</span>
                <img class="avatar" src="<?= htmlspecialchars($user['avatar_url']) ?>" alt="avatar">
                <span class="nome"><?= htmlspecialchars($user['nome']) ?></span>
                <span class="score"><?= $user['score'] ?> Score</span>
            </div>
        <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="./js/config_global.js"></script>
<script>
function mostrarRanking(tipo) {
    document.querySelectorAll('.ranking-list').forEach(div => div.classList.add('hidden'));
    document.getElementById('ranking-' + tipo).classList.remove('hidden');

    document.querySelectorAll('.ranking-buttons button').forEach(btn => btn.classList.remove('active'));
    event.target.classList.add('active');
}
</script>

</body>
</html>
