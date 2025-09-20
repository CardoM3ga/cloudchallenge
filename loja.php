<?php
session_start();
require_once 'conexao.php';

// garante que só entra logado
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];

// busca dados do usuário (pra mostrar pontos atuais, etc)
$stmt = $pdo->prepare("SELECT pontos FROM Usuarios_TBL WHERE id_usuario = ?");
$stmt->execute([$id_usuario]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// busca todos os produtos
$stmt = $pdo->query("SELECT * FROM Produtos_TBL");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Loja - CloudChallenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="./css/reset.css">
    <link rel="stylesheet" type="text/css" href="./css/menu.css">
    <link rel="stylesheet" type="text/css" href="./css/loja.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
</head>
<body>
<div class="content">
    <?php include_once 'menu_lateral.php'; ?>

        <div class="loja-content">
            <div class="loja-header">
                <h2>Loja</h2>
            </div>
            <div class="saldo">
                <i class="fa-solid fa-money-bill"></i> 
                Seus pontos: <strong><?= htmlspecialchars($usuario['pontos']) ?></strong>
            </div>
            <div class="loja">
                
                <?php if (isset($_GET['msg'])): ?>
                     <!-- Toast -->
                    <?php if (isset($_GET['msg'])): ?>
                        <!-- Toast -->
                        <div id="toast" class="toast"></div>
                        <script>
                            document.addEventListener("DOMContentLoaded", () => {
                                showToast("<?= htmlspecialchars($_GET['msg']) ?>");
                            });
                        </script>
                    <?php endif; ?>
                <?php endif; ?>
                <?php foreach ($produtos as $p): ?>
                    <div class="produto">
                        <img class="image_avatar" src="<?= htmlspecialchars($p['url_imagem']) ?>" alt="<?= htmlspecialchars($p['nome']) ?>">
                        <h3><?= htmlspecialchars($p['nome']) ?></h3>
                        <p class="preco"><i class="fa-solid fa-coins"></i> <?= htmlspecialchars($p['preco']) ?></p>

                        <form method="POST" action="comprar.php">
                            <input type="hidden" name="id_produto" value="<?= $p['id_produto'] ?>">
                            <button type="submit" class="btn-comprar">Comprar</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
</div>
<script src="./js/config_global.js"></script>
<script>
    //mostrar mensagem
            function showToast(message) {
                const toast = document.getElementById("toast");
                toast.textContent = message;
                toast.classList.add("show");

                setTimeout(() => {
                    toast.classList.remove("show");
                }, 3000); // some depois de 3s
            }
</script>
</body>
</html>
