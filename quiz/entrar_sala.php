<?php
session_start();
require '../conexao.php';

if (!isset($_SESSION['usuario'])) {
    die("Precisa estar logado.");
}

$id_usuario = $_SESSION['usuario']['id'];
$pin = $_POST['pin'] ?? null;

if (!$pin) {
    die("PIN inválido");
}

// busca partida
$stmt = $pdo->prepare("SELECT * FROM Partidas_TBL WHERE id_partida = ? AND tipo = 'privada'");
$stmt->execute([$pin]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$partida) {
    die("Sala não encontrada");
}

// verifica se já tem 2 jogadores
if ($partida['id_participante2']) {
    die("Essa sala já está cheia!");
}

// cria participante para o segundo jogador
$stmt = $pdo->prepare("
    INSERT INTO Participantes_TBL (id_partida, id_usuario, pontuacao, posicao_final, experiencia)
    VALUES (?, ?, 0, 0, 0)
");
$stmt->execute([$pin, $id_usuario]);
$id_participante2 = $pdo->lastInsertId();
$_SESSION['id_participante'] = $pdo->lastInsertId();

// atualiza a partida com o segundo participante
$stmt = $pdo->prepare("
    UPDATE Partidas_TBL SET id_participante2 = ?, status = 'em_andamento' WHERE id_partida = ?
");
$stmt->execute([$id_participante2, $pin]);

// redireciona para partida
header("Location: gerar_quiz.php?id_partida=" . $pin);
exit;
