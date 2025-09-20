<?php
session_start();
require_once 'conexao.php';

// Exemplo: buscar aulas do banco
$stmt = $pdo->prepare("SELECT id_aula, ordem, tipo, url, descricao, titulo  FROM Aulas_TBL ORDER BY id_aula ASC");
$stmt->execute();
$aulas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aulas - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/aulas.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
</head>
<body>
<div class="content">
   <?php include_once 'menu_lateral.php'; ?>

    <div class="main">
        <div class="aulas-container">
            <h1>Aulas</h1>
            <div class="lista-aulas">
                <div class="card-titles">
                        <div class="col inicio">Aula</div>
                        <div class="col titulo">Tema</div>
                        <div class="col descricao">Resumo</div>
                        <div class="col tipo">Nível</div>
                    </div>
                <?php foreach ($aulas as $index => $aula): ?>
                <a href="aula.php?id=<?= $aula['id_aula'] ?>">
                    <div class="card-aula">
                        <div class="col inicio">Aula <?= $index + 1 ?></div>
                        <div class="col titulo"><?= htmlspecialchars($aula['titulo']) ?></div>
                        <div class="col descricao"><?= htmlspecialchars($aula['descricao']) ?></div>
                        <div class="col tipo"><?= htmlspecialchars($aula['tipo']) ?></div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
    <script src="./js/config_global.js"></script>
</body>
</html>
