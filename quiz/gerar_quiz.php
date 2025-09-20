<?php
session_start();
require '../conexao.php';

$id_partida = $_GET['id_partida'] ?? null;
if (!$id_partida) die("Erro: id_partida não informado.");

// Pega o id_quiz da partida
$stmt = $pdo->prepare("SELECT id_quiz FROM Partidas_TBL WHERE id_partida = ?");
$stmt->execute([$id_partida]);
$id_quiz = $stmt->fetchColumn();

if (!$id_quiz) {
    // Se não existe quiz ainda, cria
    $stmt = $pdo->query("SELECT id_pergunta FROM Perguntas_TBL ORDER BY RAND() LIMIT 5");
    $perguntas = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $stmt = $pdo->prepare("
        INSERT INTO Quizzes_TBL (descricao, tempo_limite, dificuldade, id_pergunta1, id_pergunta2, id_pergunta3, id_pergunta4, id_pergunta5)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([
        'Quiz competitivo',
        60, // tempo em segundos
        1,
        $perguntas[0],
        $perguntas[1],
        $perguntas[2],
        $perguntas[3],
        $perguntas[4]
    ]);
    $id_quiz = $pdo->lastInsertId();

    // Atualiza partida
    $stmt = $pdo->prepare("UPDATE Partidas_TBL SET id_quiz = ? WHERE id_partida = ?");
    $stmt->execute([$id_quiz, $id_partida]);
}

// Redireciona para responder_quiz.php com o mesmo id_quiz
header("Location: responder_quiz.php?id_quiz=$id_quiz&id_partida=$id_partida");
exit;

?>
