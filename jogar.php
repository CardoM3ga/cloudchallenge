<?php
session_start();
require_once 'conexao.php';

$id_participante = $_SESSION['id_participante'] ?? null;
if (!$id_participante) die("Erro: participante não encontrado!");

// Só define exercícios e a questão atual se ainda não tiver
// Essa lógica garante que os exercícios só sejam carregados uma vez por partida
if (!isset($_SESSION['exercicios']) || !isset($_SESSION['atual'])) {
    $stmt = $pdo->query("SELECT * FROM Exercicios_TBL ORDER BY RAND() LIMIT 5");
    $_SESSION['exercicios'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $_SESSION['atual'] = 0;
}

$index = $_SESSION['atual'];

// Se terminou todas as questões
if ($index >= count($_SESSION['exercicios'])) {
    header("Location: finalizar.php");
    exit;
}

$exercicio = $_SESSION['exercicios'][$index];


// Pega alternativas do exercício atual
$stmt = $pdo->prepare("SELECT * FROM Alternativas_TBL WHERE id_exercicio = ?");
$stmt->execute([$exercicio['id_exercicio']]);
$alternativas = $stmt->fetchAll(PDO::FETCH_ASSOC);

shuffle($alternativas); // embaralha o array de alternativas
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <title>Quiz</title>
    <link rel="stylesheet" href="./css/reset.css">
    <link rel="stylesheet" href="./css/jogar.css">
    <link rel="stylesheet" href="./css/dark_mode.css">
    
</head>
<body>

<div class="quiz-container">
    <p><strong>Questão <?php echo $index + 1; ?>/<?php echo count($_SESSION['exercicios']); ?></strong></p>
    <h2><?php echo $exercicio['descricao']; ?></h2>
    <form method="POST" action="responder.php" id="quizForm">
        <?php foreach ($alternativas as $alt): ?>
            <div class="option"
                 data-id="<?php echo $alt['id_alternativa']; ?>"
                 data-correta="<?php echo $alt['correta']; ?>">
                <?php echo $alt['texto']; ?>
            </div>
        <?php endforeach; ?>
        <input type="hidden" name="resposta" id="respostaInput">
        <input type="hidden" name="id_exercicio" value="<?php echo $exercicio['id_exercicio']; ?>">
    </form>
</div>

<script>
const options = document.querySelectorAll(".option");
const respostaInput = document.getElementById("respostaInput");
const form = document.getElementById("quizForm");
let answered = false;

options.forEach(option => {
    option.addEventListener("click", () => {
        if (answered) return;

        const isCorrect = option.dataset.correta == "1";
        option.classList.add(isCorrect ? "correct" : "wrong");
        respostaInput.value = option.dataset.id;
        answered = true;

        options.forEach(o => {
            if (o !== option) o.style.pointerEvents = "none";
        });

        setTimeout(() => {
            form.submit();
        }, 800);
    });
});
</script>
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