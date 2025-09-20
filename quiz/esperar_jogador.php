<?php
session_start();
require '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    die("Precisa estar logado.");
}

$id_partida = $_GET['id_partida'] ?? 0;
if (!$id_partida) die("PIN inválido");
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.json">
<title>CloudChallenge - Sala Privada</title>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../css/esperar_jogador.css">
</head>
<body>
<div class="card">
    
    <img class="img-logo" src="../assets/images/logo.png" alt="Logo do CloudChallenge">
    <p>Sua sala privada está pronta!</p>
    <div>PIN da sua sala:</div>
    <span id="pin"><?php echo $id_partida; ?></span>
    <div id="status">Aguardando outro jogador entrar...</div>
</div>

<script>
const id_partida = <?php echo $id_partida; ?>;
const statusEl = document.getElementById('status');

const interval = setInterval(async () => {
    try {
        let res = await fetch(`verifica_sala.php?id_partida=${id_partida}`);
        let data = await res.json();

        if(data.status === "ok" && data.temJogador2) {
            clearInterval(interval);
            statusEl.innerText = "Jogador 2 entrou! Redirecionando...";
            setTimeout(() => {
                window.location.href = `gerar_quiz.php?id_partida=${id_partida}`;
            }, 1000);
        } else {
            statusEl.innerText = "Aguardando jogador 2...";
        }
    } catch (err) {
        console.error("Erro ao verificar sala:", err);
        statusEl.innerText = "Erro ao verificar sala...";
    }
}, 2000);


    if ("serviceWorker" in navigator) {
        window.addEventListener("load", () => {
            navigator.serviceWorker.register("../sw.js")
            .then((reg) => console.log("Service Worker registrado!", reg))
            .catch((err) => console.log("Falha ao registrar SW:", err));
        });
    }

</script>
</body>
</html>

