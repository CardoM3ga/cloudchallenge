<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_logado = $_SESSION['usuario']['id'];

// --- BUSCAR DADOS PARA O PÓDIO --- 
$sql_podio = "
    SELECT
        u.nome,
        u.score,
        u.avatar_url
    FROM Usuarios_TBL u
    ORDER BY u.score DESC
    LIMIT 3
";
$stmt_podio = $pdo->query($sql_podio);
$podio = $stmt_podio->fetchAll(PDO::FETCH_ASSOC);

// --- BUSCAR ÚLTIMAS 5 PARTIDAS DO USUÁRIO ---
$sql_partidas = "
    SELECT 
        pa.id_partida,
        pa.pontuacao1,
        pa.pontuacao2,
        pa.id_participante1,
        pa.id_participante2,
        u_oponente.nome AS nome_oponente,
        u_oponente.avatar_url AS avatar_oponente,
        p_usuario.id_participante
    FROM Partidas_TBL pa
    JOIN Participantes_TBL p_usuario 
        ON (pa.id_participante1 = p_usuario.id_participante OR pa.id_participante2 = p_usuario.id_participante)
        AND p_usuario.id_usuario = ?
    JOIN Participantes_TBL p_oponente 
        ON pa.id_partida = p_oponente.id_partida 
        AND p_oponente.id_usuario != ?
    JOIN Usuarios_TBL u_oponente 
        ON p_oponente.id_usuario = u_oponente.id_usuario
    ORDER BY pa.data DESC
    LIMIT 5
";
$stmt_partidas = $pdo->prepare($sql_partidas);
$stmt_partidas->execute([$id_logado, $id_logado]);
$partidas_usuario = $stmt_partidas->fetchAll(PDO::FETCH_ASSOC);


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/index.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
</head>
<body>
<div class="content">
    <?php include_once 'menu_lateral.php'; ?>

    <div class="main">

        <!-- Banner + Pódio -->
        <div class="banner">
            <div class="podium-container">
                <?php
                if (!empty($podio)) {
                    $posicoes = [1, 0, 2]; // ordem de exibição
                    foreach ($posicoes as $i => $pos) {
                        $jogador = $podio[$pos];

                        $classe_coroa = '';
                        $classe_borda = '';
                        if ($pos == 0) { $classe_coroa = 'coroa-ouro'; $classe_borda = 'borda-ouro'; }
                        if ($pos == 1) { $classe_coroa = 'coroa-prata'; $classe_borda = 'borda-prata'; }
                        if ($pos == 2) { $classe_coroa = 'coroa-bronze'; $classe_borda = 'borda-bronze'; }

                        echo "
                        <div class='jogador-podio pos-{$pos}'>
                            <div class='icone-jogador'>
                                <img src='{$jogador['avatar_url']}' alt='Avatar de {$jogador['nome']}' class='{$classe_borda}'>
                                <i class='fa-solid fa-crown {$classe_coroa}'></i>
                            </div>
                            <div class='info-jogador'>
                                <span class='nome-jogador'>{$jogador['nome']}</span><br>
                                <span class='pontos-jogador'>{$jogador['score']} score</span>
                            </div>
                        </div>";
                    }
                }
                ?>
            </div>
        </div>

        <!-- Histórico -->
        <div class="matches-container">
            <div class="matches-header">
                <h2>Histórico</h2>
                <a href="amigos.php" class="btn-friends">
                    <i class="fa-solid fa-user-group"></i> Amigos
                </a>
            </div>
            <hr>
            <div class="matches">
                <?php
            if (!empty($partidas_usuario)) {
    foreach ($partidas_usuario as $p) {
        // descobrir se o usuário logado era participante1 ou participante2
        if ($p['id_participante'] == $p['id_participante1']) {
            $pontuacao_usuario = $p['pontuacao1'];
            $pontuacao_oponente = $p['pontuacao2'];
        } else {
            $pontuacao_usuario = $p['pontuacao2'];
            $pontuacao_oponente = $p['pontuacao1'];
        }

        // calcular resultado (só pra classe)
        if ($pontuacao_usuario > $pontuacao_oponente) {
            $resultado_class = "win";
        } elseif ($pontuacao_usuario < $pontuacao_oponente) {
            $resultado_class = "lose";
        } else {
            $resultado_class = "draw";
        }

        // avatar do oponente (fallback para arquivo local)
        $avatar_oponente_raw = !empty($p['avatar_oponente']) ? $p['avatar_oponente'] : 'assets/images/default-avatar.png';
        $avatar_oponente = htmlspecialchars($avatar_oponente_raw, ENT_QUOTES, 'UTF-8');
        $nome_oponente = htmlspecialchars($p['nome_oponente'], ENT_QUOTES, 'UTF-8');

        echo "
            <div class='match {$resultado_class}'>
                <div class='user'>
                    <img src=\"{$avatar_oponente}\" alt='Avatar de {$nome_oponente}' class='avatar'>
                    <span>{$nome_oponente}</span>
                </div>
                <div class='score'>
                    {$pontuacao_usuario} - {$pontuacao_oponente}
                </div>
                <button class='btn-play' onclick=\"window.location.href='quiz/resultado_partida.php?id_partida={$p['id_partida']}'\">
                    Ver detalhes
                </button>
            </div>";
        }
        } else {
            echo "<p>Você ainda não jogou nenhuma partida.</p>";
        }

                ?>
            </div>
        </div>

    </div>
</div>

<script>
    function directToPerfil(){ window.location.href = 'perfil.php' }
    function directToAulas(){ window.location.href = 'aulas.php' }
    function directToIndex(){ window.location.href = 'index.php' }
</script>

</body>
</html>