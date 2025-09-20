<?php
session_start();
require '../conexao.php';

if (!isset($_GET['id_quiz'])) {
    die(json_encode(['error' => 'id_quiz não informado']));
}

$id_quiz = $_GET['id_quiz'];

// Pega perguntas do quiz
$stmt = $pdo->prepare("
    SELECT q.id_pergunta, q.enunciado, a.id_alternativa, a.texto, a.correta
    FROM Quizzes_TBL qt
    JOIN Perguntas_TBL q ON q.id_pergunta = qt.id_pergunta1
    OR q.id_pergunta = qt.id_pergunta2
    OR q.id_pergunta = qt.id_pergunta3
    OR q.id_pergunta = qt.id_pergunta4
    OR q.id_pergunta = qt.id_pergunta5
    JOIN Alternativas_TBL a ON a.id_pergunta = q.id_pergunta
    WHERE qt.id_quiz = ?
    ORDER BY q.id_pergunta, a.id_alternativa
");
$stmt->execute([$id_quiz]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Organiza perguntas com alternativas
$quiz = [];
foreach ($rows as $row) {
    $pid = $row['id_pergunta'];
    if (!isset($quiz[$pid])) {
        $quiz[$pid] = [
            'id_pergunta' => $pid,
            'enunciado' => $row['enunciado'],
            'alternativas' => []
        ];
    }
    $quiz[$pid]['alternativas'][] = [
        'id_alternativa' => $row['id_alternativa'],
        'texto' => $row['texto'],
        'correta' => $row['correta'] // true ou false
    ];
}

// Retorna JSON
header('Content-Type: application/json');
echo json_encode(array_values($quiz));