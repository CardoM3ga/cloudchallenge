<?php
session_start();
require_once 'conexao.php';

// detectar id do usuário em duas formas possíveis (robusto)
if (isset($_SESSION['usuario']) && isset($_SESSION['usuario']['id'])) {
    $id_usuario = $_SESSION['usuario']['id'];
} elseif (isset($_SESSION['id_usuario'])) {
    $id_usuario = $_SESSION['id_usuario'];
} else {
    // sem sessão -> volta pro login
    header('Location: login.php');
    exit;
}

// Buscar amigos (ajuste nomes de colunas/tabelas se necessário)
$sql = "
    SELECT u.id_usuario, u.nome, u.avatar_url, u.nivel
    FROM Amizades_TBL a
    JOIN Usuarios_TBL u ON (
        (a.id_usuario1 = :id AND u.id_usuario = a.id_usuario2)
        OR
        (a.id_usuario2 = :id AND u.id_usuario = a.id_usuario1)
    )
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $id_usuario]);
$amigos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Amigos - CloudChallenge</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" type="text/css" href="./css/reset.css">
<link rel="stylesheet" type="text/css" href="./css/menu.css">
<link rel="stylesheet" type="text/css" href="./css/amigos.css">
</head>
<body>
<div class="content">
    <?php include_once 'menu_lateral.php'; ?>

    <div class="main">
        <div class="banner"></div>
        
        <div class="friends-container">
            <div class="friends-header">
                <h2>Meus Amigos (<?php echo count($amigos); ?>)</h2>
                <a href="adicionar_amigos.php" class="btn-back">
                    <i class="fa-solid fa-user-group"></i> Encontrar usuários
                </a>
            </div>
            <hr>
            <div class="friends-list">
                <?php if (!empty($amigos)): ?>
                    <?php foreach ($amigos as $amigo): ?>
                        <?php
                            $avatar = !empty($amigo['avatar_url']) ? $amigo['avatar_url'] : './assets/images/default-avatar.png';
                            $nivel = isset($amigo['nivel']) ? intval($amigo['nivel']) : 1;
                        ?>
                        <div class="friend-card">
                            <div class="friend-info">
                                <img src="<?= htmlspecialchars($avatar) ?>" alt="Avatar de <?= htmlspecialchars($amigo['nome']) ?>">
                                <div class="friend-text">
                                    <span class="friend-name"><?= htmlspecialchars($amigo['nome']) ?></span>
                                    <span class="friend-level">Lvl. <?= $nivel ?></span>
                                </div>
                            </div>
                            <div class="friend-actions">
                                <button class="btn-view" onclick="window.location.href='perfil_amigo.php?id=<?= intval($amigo['id_usuario']) ?>'">
                                    Ver Perfil
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Você ainda não tem amigos adicionados.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
