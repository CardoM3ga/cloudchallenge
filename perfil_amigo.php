<?php
session_start();
require_once 'conexao.php';

// garantir login
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// pegar id do amigo
if (!isset($_GET['id'])) {
    echo "Amigo não especificado!";
    exit;
}

$id_amigo = intval($_GET['id']);

// buscar dados do amigo
$stmt = $pdo->prepare("
    SELECT nome, email, avatar_url, pontos, nivel, score 
    FROM Usuarios_TBL 
    WHERE id_usuario = ?
");
$stmt->execute([$id_amigo]);
$amigo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$amigo) {
    echo "Amigo não encontrado!";
    exit;
}

// contar amigos do amigo
$stmtAmigos = $pdo->prepare("
    SELECT COUNT(*) as total_amigos 
    FROM Amizades_TBL 
    WHERE (id_usuario1 = :id OR id_usuario2 = :id) 
      AND status = 'aceito'
");
$stmtAmigos->execute(['id' => $id_amigo]);
$amigos_count = $stmtAmigos->fetch(PDO::FETCH_ASSOC);
$total_amigos = $amigos_count ? $amigos_count['total_amigos'] : 0;

// buscar avatares do amigo
$stmt = $pdo->prepare("
    SELECT p.id_produto, p.nome, p.url_imagem
    FROM Itens_TBL i
    JOIN Produtos_TBL p ON i.id_produto = p.id_produto
    WHERE i.id_usuario = ?
");
$stmt->execute([$id_amigo]);
$avatares = $stmt->fetchAll(PDO::FETCH_ASSOC);

// calcular partidas jogadas
$stmtPartidas = $pdo->prepare("
    SELECT COUNT(*) as total 
    FROM Participantes_TBL 
    WHERE id_usuario = ?
");
$stmtPartidas->execute([$id_amigo]);
$partidas = $stmtPartidas->fetch(PDO::FETCH_ASSOC);
$total_partidas = $partidas ? $partidas['total'] : 0;

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($amigo['nome']) ?> - Perfil</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/perfil.css">
</head>
<body>
<div class="content">
    <?php include_once 'menu_lateral.php'; ?>

    <div class="content_perfil">
        <div class="head">
            <div class="top_content_perfil">
                <button onclick="history.back()"><i class="seta fa-solid fa-arrow-left"></i></button>
            </div>
            <div class="mid_content_perfil">
                <span class="avatar">
                    <img src="<?= htmlspecialchars($amigo['avatar_url']) ?>" alt="Avatar do amigo">
                </span>
                <div class="avatar_content">
                    <span class="name"><?= htmlspecialchars($amigo['nome']) ?></span>
                    <span class="friends"><?= $total_amigos ?> amigos</span>
                </div>
            </div>
        </div>

        <div class="content_infos">
            <h2>Performance</h2>
            <div class="infos">
                <div class="info_box">
                    <div class="title">Partidas Jogadas</div>
                    <span class="data"><?= $total_partidas ?></span>
                </div>
                <div class="info_box">
                    <div class="title">Pontos</div>
                    <span class="data"><?= htmlspecialchars($amigo['pontos']) ?></span>
                </div>
                <div class="info_box">
                    <div class="title">Nível</div>
                    <span class="data"><?= htmlspecialchars($amigo['nivel']) ?></span>
                </div>
                <div class="info_box">
                    <div class="title">Score</div>
                    <span class="data"><?= htmlspecialchars($amigo['score']) ?></span>
                </div>
            </div>
        </div>

        <div class="content_avatares">
            <h2>Avatares</h2>
            <div class="avatars_grid">
                <!-- avatar inicial padrão -->
                <div class="item_avatar">
                    <img src="./assets/images/avatares/27.png" alt="Avatar padrão">
                    <div class="texto">João</div>
                </div>
                <?php if (count($avatares) > 0): ?>
                    <?php foreach ($avatares as $avatar): ?>
                        <div class="item_avatar">
                            <img src="<?= htmlspecialchars($avatar['url_imagem']) ?>" alt="Avatar">
                            <div class="texto"><?= htmlspecialchars($avatar['nome']) ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>