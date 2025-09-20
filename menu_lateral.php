<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require 'conexao.php';

$id_logado = $_SESSION['usuario']['id'];
$sql_usuario = "SELECT nome, avatar_url FROM Usuarios_TBL WHERE id_usuario = ?";
$stmt_usuario = $pdo->prepare($sql_usuario);
$stmt_usuario->execute([$id_logado]);
$usuario_logado = $stmt_usuario->fetch(PDO::FETCH_ASSOC);

require_once 'validaXP.php';

?>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="manifest.json">
<meta name="theme-color" content="#000000">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
<link rel="stylesheet" href="/css/menu.css">
<link rel="stylesheet" href="/css/reset.css">
<link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
<body>
<div class="side-menu">
    <div class="top">
        <div class="logo">
            <img src="./assets/images/logo.png" alt="CloudChallenge Logo">
        </div>
        <div class="menu-item perfil-usuario" onclick="directToPerfil()">
            <img src="<?php echo htmlspecialchars($usuario_logado['avatar_url']); ?>" 
                alt="Avatar de <?php echo htmlspecialchars($usuario_logado['nome']); ?>" 
                class="avatar-usuario">
            <span class="nome-usuario"><?php echo htmlspecialchars($usuario_logado['nome']); ?></span>
        </div>

    </div>

    <div class="divider"></div>

    <div class="mid">
        <div class="menu-item">
            <button onclick="directToIndex()">
                <i class="fa-solid fa-house"></i><span>Início</span>
            </button>
        </div>
        <div class="menu-item">
            <button onclick="directToLoja()">
                <i class="fa-solid fa-store"></i><span>Loja</span>
            </button>
        </div>
        <div class="menu-item">
            <button onclick="directToPlayWithFriend()">
                <i class="fa-solid fa-play"></i><span>Jogue com um amigo</span>
            </button>
        </div>
        <div class="menu-item">
            <button onclick="directToRanking()">
                <i class="fa-solid fa-trophy"></i><span>Ranking</span>
            </button>
        </div>
        <div class="menu-item">
            <button onclick="directToAulas()">
                <i class="fa-solid fa-book"></i><span>Aulas</span>
            </button>
        </div>
        <button class="play">Jogar</button>
    </div>

    <!-- Botão Hamburger -->
    <div class="hamburger" onclick="toggleMenu()">
        <i class="fa-solid fa-bars"></i>
    </div>


    <div class="divider"></div>

    <div class="bot">
        <div class="menu-item">
            <button onclick="directToConfig()">
                <i class="fa-solid fa-gear"></i><span>Configurações</span>
            </button>
        </div>
    </div>
</div>

<!-- Modal -->
<div id="modalJogar" class="modal-modos">
    <div class="modal-content-modos">
        <span class="close">&times;</span>
        <h2>Escolha o modo de jogo</h2>
        <div class="modes">
            <button class="btn-modal-modos" onclick="startGameSolo()">Modo Solo</button>
            <button class="btn-modal-modos" onclick="startGameComp()">Competitivo</button>
        </div>
    </div>
</div>


<script src="./js/config_global.js"></script>
<script>
    function directToPerfil(){ window.location.href = 'perfil.php' }
    function directToAulas(){ window.location.href = 'aulas.php' }
    function directToIndex(){ window.location.href = 'index.php' }
    function directToLoja(){ window.location.href = 'loja.php' }
    function directToPlayWithFriend(){ window.location.href = 'participe_jogo.php'}
    function directToRanking(){ window.location.href = 'ranking.php' }
    function directToConfig(){ window.location.href = 'config.php'}

    // Pega modal e botões
    const modal = document.getElementById('modalJogar');
    const btnJogar = document.querySelector('.play');
    const spanClose = document.querySelector('.close');

    // Abre modal
    btnJogar.onclick = function() {
        modal.style.display = "block";
    }

    // Fecha modal quando clica no X
    spanClose.onclick = function() {
        modal.style.display = "none";
    }

    // Fecha modal quando clica fora
    window.onclick = function(event) {
        if(event.target == modal) {
            modal.style.display = "none";
        }
    }

    // Função pra iniciar o jogo
    function startGameSolo() {
        window.location.href = 'criar_partida.php'
    }

    function startGameComp() {
        window.location.href = './quiz/entrar_competitivo.php'
    }

    function toggleMenu() {
        const menu = document.querySelector('.side-menu');
        menu.classList.toggle('active');
    }

    if ("serviceWorker" in navigator) {
        window.addEventListener("load", () => {
            navigator.serviceWorker.register("CloudChallenge/sw.js")
            .then((reg) => console.log("Service Worker registrado!", reg))
            .catch((err) => console.log("Falha ao registrar SW:", err));
        });
    }


</script>

</body>