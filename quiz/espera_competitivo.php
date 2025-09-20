<?php
session_start();
require '../conexao.php';

$id_partida = $_GET['id_partida'];
$id_participante = $_SESSION['id_participante'];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.json">
<title>Esperando adversário - Competitivo</title>
<link rel="stylesheet" href="../css/reset.css">
<link rel="stylesheet" href="../css/esperar_competitivo.css">
</head>
<body>
<div class="container">
    <h1>Esperando adversário...</h1>
    <div class="spinner"></div>
    <div id="status">Conectando...</div>
    <div class="note">A página atualizará automaticamente quando o adversário entrar.</div>
</div>

<script>
async function verificar() {
    try {
        let res = await fetch('verificar_status_partida.php?id_partida=<?php echo $id_partida; ?>');
        let data = await res.json();

        if (data.outro_jogador) {
            // Quando o outro jogador entra, redireciona para gerar quiz
            window.location.href = 'gerar_quiz.php?id_partida=<?php echo $id_partida; ?>';
        } else {
            document.getElementById('status').innerHTML = 'Ainda esperando outro jogador...';
        }
    } catch (e) {
        console.error(e);
        document.getElementById('status').innerHTML = 'Erro ao verificar status, tentando novamente...';
    }
}

// Checa a cada 3 segundos
setInterval(verificar, 3000);

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
