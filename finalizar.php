<?php
session_start();
require_once 'conexao.php';

$id_participante = $_SESSION['id_participante'] ?? null;
if (!$id_participante) die("Erro: participante não encontrado!");

// pega pontuação final e experiencia
$stmt = $pdo->prepare("SELECT COALESCE(pontuacao,0) as pontuacao, COALESCE(experiencia,0) as experiencia FROM Participantes_TBL WHERE id_participante = ?");
$stmt->execute([$id_participante]);
$result = $stmt->fetch(PDO::FETCH_ASSOC);

$pontuacao = $result['pontuacao'] ?? 0;
$experiencia = $result['experiencia'] ?? 0;

$id_usuario = $_SESSION['usuario']['id'];

// atualiza na tabela de usuários
$stmt = $pdo->prepare("UPDATE Usuarios_TBL 
                       SET pontos = COALESCE(pontos,0) + ?, 
                           experiencia = COALESCE(experiencia,0) + ? 
                       WHERE id_usuario = ?");
$stmt->execute([$pontuacao, $experiencia, $id_usuario]);

// fecha a partida
if (isset($_SESSION['id_partida'])) {
    $stmt = $pdo->prepare("UPDATE Partidas_TBL SET status = 'finalizada' WHERE id_partida = ?");
    $stmt->execute([$_SESSION['id_partida']]);
}

// limpa sessão
unset($_SESSION['exercicios'], $_SESSION['atual']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <title>Quiz Finalizado</title>
    <link rel="stylesheet" href="./css/reset.css">
    <link rel="stylesheet" href="./css/finalizar.css">
</head>
<body>

<div class="final-container">
    <h2>🎉 Quiz Finalizado!</h2>
    <p><strong>Pontos:</strong> <?php echo $pontuacao; ?></p>
    <p><strong>Experiência:</strong> <?php echo $experiencia; ?></p>
    <a href="criar_partida.php" class="button">Jogar de novo 🔄</a>
    <a href="index.php" class="button">Voltar ao Menu 🏠</a>
</div>

<script>
    if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("/CloudChallenge/sw.js")
      .then((reg) => console.log("Service Worker registrado!", reg))
      .catch((err) => console.log("Falha ao registrar SW:", err));
  });
}
</script>

</body>
</html>
