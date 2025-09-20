<?php
session_start();
require '../conexao.php';

$id_quiz = $_GET['id_quiz'] ?? null;
$id_partida = $_GET['id_partida'] ?? null;
$id_participante = $_SESSION['id_participante'] ?? null;

if (!$id_quiz || !$id_participante || !$id_partida) {
    die("Erro: quiz, participante ou partida não informado.");
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json">
    <title>Quiz Competitivo - Cloud Challenge</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/responder_quiz.css">
</head>
<body>

<div class="quiz-box">
    <div class="progress" id="progress"></div>
    <div id="quizContainer"></div>
</div>

<script>
const id_quiz = <?php echo $id_quiz; ?>;
const id_partida = <?php echo $id_partida; ?>;
let quiz = [];
let currentIndex = 0;
let respostasUsuario = {};

async function carregarQuiz() {
    const res = await fetch('pegar_quiz.php?id_quiz=' + id_quiz);
    quiz = await res.json();
    mostrarPergunta();
}

function mostrarPergunta() {
    if (currentIndex >= quiz.length) {
        enviarRespostas();
        return;
    }

    const pergunta = quiz[currentIndex];
    const container = document.getElementById('quizContainer');
    container.innerHTML = '';

    document.getElementById('progress').innerText = `Pergunta ${currentIndex+1} / ${quiz.length}`;

    const pEl = document.createElement('div');
    pEl.classList.add('pergunta');
    pEl.innerText = pergunta.enunciado;
    container.appendChild(pEl);

    let alternativas = [...pergunta.alternativas];
    alternativas.sort(() => Math.random() - 0.5);

    alternativas.forEach(a => {
        const altEl = document.createElement('div');
        altEl.classList.add('alternativa');
        altEl.innerText = a.texto;
        altEl.addEventListener('click', () => selecionarAlternativa(pergunta.id_pergunta, a));
        container.appendChild(altEl);
    });
}

function selecionarAlternativa(id_pergunta, alternativa) {
    respostasUsuario[`pergunta_${id_pergunta}`] = alternativa.id_alternativa;

    const container = document.getElementById('quizContainer');
    Array.from(container.getElementsByClassName('alternativa')).forEach(el => {
        el.style.pointerEvents = 'none';
        if (el.innerText === alternativa.texto) {
            el.classList.add(alternativa.correta ? 'correct' : 'wrong');
        }
    });

    setTimeout(() => {
        currentIndex++;
        mostrarPergunta();
    }, 1200);
}

async function enviarRespostas() {
    const res = await fetch('salvar_respostas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_quiz: id_quiz,
            id_partida: id_partida,
            respostas: respostasUsuario
        })
    });

    const data = await res.json();
    if (data.success) {
        window.location.href = 'finalizar_quiz.php?id_quiz=' + id_quiz + '&id_partida=' + id_partida;
    } else {
        alert(data.error || 'Erro ao enviar respostas.');
    }
}

carregarQuiz();


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
