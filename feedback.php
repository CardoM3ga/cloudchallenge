<?php
session_start();

if (!isset($_SESSION['feedback'])) {
    header("Location: jogar.php");
    exit;
}

$feedback = $_SESSION['feedback'];
unset($_SESSION['feedback']); // limpa feedback depois de mostrar

function youtubeEmbed($url) {
    // Pega o ID do vídeo
    preg_match("/(?:v=|\/)([0-9A-Za-z_-]{11}).*/", $url, $matches);
    $id = $matches[1] ?? '';
    if ($id) {
        return "https://www.youtube.com/embed/$id";
    }
    return '';
}

$embed_url = youtubeEmbed($feedback['url_recurso']);
?>



<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<link rel="manifest" href="/manifest.json">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Feedback</title>
<link rel="stylesheet" href="./css/reset.css">
<link rel="stylesheet" href="./css/feedback.css">
</head>
<body>

<div class="card">
    <?php if ($feedback['acertou']): ?>
        <h2 class="success">✅ Você acertou!</h2>
    <?php else: ?>
        <h2 class="error">❌ Você errou!</h2>
    <?php endif; ?>

    <p><strong>Explicação:</strong> <?= htmlspecialchars($feedback['explicacao']) ?></p>

    <?php if (!empty($embed_url)): ?>
        <div class="recurso-container">
            <iframe src="<?= htmlspecialchars($embed_url) ?>" allowfullscreen></iframe>
        </div>
    <?php endif; ?>


    <form method="get" action="jogar.php">
        <button type="submit" class="button">Próxima questão ➡️</button>
    </form>
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
