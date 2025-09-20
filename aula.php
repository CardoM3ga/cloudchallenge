<?php
session_start();
require_once 'conexao.php';

// Pega o ID da aula via URL
$id_aula = $_GET['id'] ?? null;

if (!$id_aula) {
    echo "Aula não encontrada.";
    exit;
}

// Busca no banco
$stmt = $pdo->prepare("SELECT * FROM Aulas_TBL WHERE id_aula = ?");
$stmt->execute([$id_aula]);
$aula = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$aula) {
    echo "Aula não encontrada.";
    exit;
}

// Extrai ID do YouTube (pra embutir no iframe)
function getYoutubeId($url) {
    preg_match("/(?:v=|be\/)([a-zA-Z0-9_-]{11})/", $url, $matches);
    return $matches[1] ?? null;
}
$videoId = getYoutubeId($aula['url']);

// Aula anterior
$stmt_prev = $pdo->prepare("SELECT id_aula FROM Aulas_TBL WHERE id_aula < ? ORDER BY id_aula DESC LIMIT 1");
$stmt_prev->execute([$id_aula]);
$aula_anterior = $stmt_prev->fetchColumn();

// Próxima aula
$stmt_next = $pdo->prepare("SELECT id_aula FROM Aulas_TBL WHERE id_aula > ? ORDER BY id_aula ASC LIMIT 1");
$stmt_next->execute([$id_aula]);
$aula_proxima = $stmt_next->fetchColumn();

?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="./css/reset.css">
<link rel="stylesheet" href="./css/menu.css">
<link rel="stylesheet" href="./css/aula.css">
<link rel="stylesheet" type="text/css" href="./css/dark_mode.css">



<div class="content">
    <?php include 'menu_lateral.php';?>
    <div class="main">
        <a href="aulas.php" class="voltar fa-solid fa-arrow-left"></a>
        <h2><?= htmlspecialchars($aula['titulo']) ?></h2>
        <?php if ($videoId): ?>
            <div class="video">
                <iframe width="560" height="315" 
                    src="https://www.youtube.com/embed/<?= $videoId ?>" 
                    frameborder="0" allowfullscreen>
                </iframe>
            </div>
        <?php else: ?>
            <p>Vídeo não disponível.</p>
        <?php endif; ?>

        <h3>Resumo</h3>
        <?php

            $descricao = htmlspecialchars($aula['descricao']);

            // Quebra o texto em frases pelo ponto final
            $frases = explode('. ', $descricao);

            // Adiciona parágrafos para cada frase
            $descricao_formatada = '';
            foreach($frases as $frase){
                $frase = trim($frase);
                if($frase) $descricao_formatada .= "<p>$frase.</p>";
            }
        ?>
        <div class="descricao">
            <?= $descricao_formatada?>
        </div>

        <div class="botoes">
            <div class="btn-botoes">
                <?php if ($aula_anterior): ?>
                    <a href="aula.php?id=<?= $aula_anterior ?>" class="btn">Anterior</a>
                <?php else: ?>
                    <button class="btn" disabled>Anterior</button>
                <?php endif; ?>

                <?php if ($aula_proxima): ?>
                    <a href="aula.php?id=<?= $aula_proxima ?>" class="btn">Próxima</a>
                <?php else: ?>
                    <button class="btn" disabled>Próxima</button>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="./js/config_global.js"></script>

