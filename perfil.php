<?php
session_start();
require_once 'conexao.php';

$id_usuario = $_SESSION['usuario']['id'];

// Buscar dados básicos do usuário
$stmt = $pdo->prepare("
    SELECT nome, email, avatar_url, pontos, nivel, score
    FROM Usuarios_TBL 
    WHERE id_usuario = ?
");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario) {
    echo "Usuário não encontrado!";
    exit;
}

// Buscar itens/avatares do usuário
$stmt = $pdo->prepare("
    SELECT p.id_produto, p.nome, p.descricao, p.url_imagem, p.tipo, i.data_aquisicao
    FROM Itens_TBL i
    JOIN Produtos_TBL p ON i.id_produto = p.id_produto
    WHERE i.id_usuario = ?
");
$stmt->execute([$id_usuario]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contar partidas jogadas
$stmtPartidas = $pdo->prepare("
    SELECT COUNT(*) AS partidas_jogadas
    FROM Participantes_TBL
    WHERE id_usuario = ?
");
$stmtPartidas->execute([$id_usuario]);
$partidas = $stmtPartidas->fetch(PDO::FETCH_ASSOC);
$partidas_jogadas = $partidas ? $partidas['partidas_jogadas'] : 0;

// Contar amigos
$stmtAmigos = $pdo->prepare("
    SELECT COUNT(*) as total_amigos 
    FROM Amizades_TBL 
    WHERE (id_usuario1 = :id OR id_usuario2 = :id) 
      AND status = 'aceito'
");
$stmtAmigos->execute(['id' => $id_usuario]);
$amigos = $stmtAmigos->fetch(PDO::FETCH_ASSOC);
$total_amigos = $amigos ? $amigos['total_amigos'] : 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/perfil.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
</head>
<body>
    <div class="content">
        
        <?php include_once 'menu_lateral.php'; ?>

        <!-- notificações -->
        <div id="notificacao" class="notificacao"></div>

        <!-- main -->
        <div class="content_perfil">
            <div class="head">
                <div class="top_content_perfil">
                    <button onclick="directToIndex()"><i class="seta fa-solid fa-arrow-left"></i></button>
                </div>
                <div class="mid_content_perfil">
                    <span class="avatar">
                        <img src="<?= htmlspecialchars($usuario['avatar_url']) ?>" alt="Foto do usuário">
                    </span>
                    <div class="avatar_content">
                        <div class="name perfil_edit">
                            <span id="nomeUsuario"><?= htmlspecialchars($usuario['nome']) ?></span>
                            <input type="text" id="inputNome" value="<?= htmlspecialchars($usuario['nome']) ?>" style="display:none;">
                            <i class="icon fa-solid fa-pencil" id="editarNome"></i>
                            <i class="icon fa-solid fa-check" id="salvarNome" style="display:none; color:green; cursor:pointer;"></i>
                        </div>
                        <span class="friends"><?= $total_amigos ?> amigos</span>
                    </div>
                </div>
                <div class="bot_content_perfil">
                    <button class="share">Compartilhar</button>
                </div>
            </div>

            <!-- Performance -->
            <div class="content_infos">
                <h2>Performance</h2>
                <div class="infos">
                    <div class="info_box">
                        <div class="title">Partidas Jogadas</div>
                        <span class="data"><?= $partidas_jogadas ?></span>
                    </div>
                    <div class="info_box">
                        <div class="title">Pontos</div>
                        <span class="data"><?= htmlspecialchars($usuario['pontos']) ?></span>
                    </div>
                    <div class="info_box">
                        <div class="title">Nível</div>
                        <span class="data"><?= htmlspecialchars($usuario['nivel']) ?></span>
                    </div>
                    <div class="info_box">
                        <div class="title">Score</div>
                        <span class="data"><?= htmlspecialchars($usuario['score']) ?></span>
                    </div>

                </div>
            </div>
            
            <!-- Avatares -->
            <div class="content_avatares">
                <h2>Avatares</h2>
                <div class="avatars_grid">
                    <button class="trocaAvatar" data-url="./assets/images/avatares/27.png">
                        <div class="item_avatar">
                            <img src="./assets/images/avatares/27.png" alt="João">
                            <div class="texto">João</div>
                        </div>
                    </button>
                    <?php if (!empty($itens)): ?>
                        <?php foreach ($itens as $item): ?>
                            <button class="trocaAvatar" data-url="<?= htmlspecialchars($item['url_imagem']) ?>">
                                <div class="item_avatar">
                                    <img src="<?= htmlspecialchars($item['url_imagem']) ?>" alt="Avatar">
                                    <div class="texto"><?= htmlspecialchars($item['nome'])?></div>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

      <script src="./js/config_global.js"></script>
    <script>
        function directToIndex(){
            window.location.href = "index.php";
        }

        function mostrarNotificacao(mensagem, tipo = "sucesso") {
            const notif = document.getElementById("notificacao");
            notif.textContent = mensagem;
            notif.className = "notificacao " + tipo; // aplica estilo
            notif.style.display = "block";

            setTimeout(() => {
                notif.style.opacity = "0";
                setTimeout(() => {
                    notif.style.display = "none";
                    notif.style.opacity = "1";
                }, 400);
            }, 2500);
        }

        // trocar avatar
        document.querySelectorAll(".trocaAvatar").forEach(button => {
            button.addEventListener("click", async () => {
                const url = button.getAttribute("data-url");

                let formData = new FormData();
                formData.append("url_imagem", url);

                let response = await fetch("trocar_avatar.php", {
                    method: "POST",
                    body: formData
                });

                let result = await response.json();

                if (result.success) {
                    mostrarNotificacao("Avatar alterado com sucesso!", "sucesso");
                    document.querySelector(".avatar img").src = result.url;
                } else {
                    mostrarNotificacao("Erro: " + result.message, "erro");
                }
            });
        });

        const editarBtn = document.getElementById("editarNome");
        const salvarBtn = document.getElementById("salvarNome");
        const nomeSpan = document.getElementById("nomeUsuario");
        const inputNome = document.getElementById("inputNome");

        editarBtn.addEventListener("click", () => {
            nomeSpan.style.display = "none";
            inputNome.style.display = "inline-block";
            editarBtn.style.display = "none";
            salvarBtn.style.display = "inline-block";
            inputNome.focus();
        });

        salvarBtn.addEventListener("click", async () => {
            const novoNome = inputNome.value.trim();

            if (!novoNome) {
                mostrarNotificacao("O nome não pode ser vazio!", "erro");
                return;
            }

            let formData = new FormData();
            formData.append("nome", novoNome);

            let response = await fetch("update_perfil.php", {
                method: "POST",
                body: formData
            });

            let result = await response.json();

            if (result.success) {
                nomeSpan.textContent = result.nome;
                mostrarNotificacao("Nome atualizado!", "sucesso");
            } else {
                mostrarNotificacao(result.message, "erro");
            }

            nomeSpan.style.display = "inline-block";
            inputNome.style.display = "none";
            editarBtn.style.display = "inline-block";
            salvarBtn.style.display = "none";
        });

    </script>
</body>
</body>
</html>
