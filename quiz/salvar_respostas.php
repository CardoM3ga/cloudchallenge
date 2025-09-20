<?php
session_start();
require '../conexao.php';

$id_participante = $_SESSION['id_participante'] ?? null;
$input = json_decode(file_get_contents('php://input'), true);

$id_quiz = $input['id_quiz'] ?? null;
$id_partida = $input['id_partida'] ?? null;
$respostas = $input['respostas'] ?? null;

if (!$id_participante || !$id_quiz || !$id_partida || !$respostas) {
    echo json_encode(['success' => false, 'error' => 'Dados incompletos']);
    exit;
}

try {
    foreach ($respostas as $key => $id_alternativa) {
        preg_match('/pergunta_(\d+)/', $key, $matches);
        $id_pergunta = $matches[1];

        // Verifica se a alternativa é correta
        $stmt = $pdo->prepare("SELECT correta FROM Alternativas_TBL WHERE id_alternativa = ?");
        $stmt->execute([$id_alternativa]);
        $correta = (int)$stmt->fetchColumn(); // 1 ou 0

        // Salva resposta
        $stmt = $pdo->prepare("
            INSERT INTO Respostas_partida_TBL 
            (id_participante, id_quiz, id_partida, id_pergunta, id_alternativa, tempo_resposta, correta)
            VALUES (?, ?, ?, ?, ?, 0, ?)
        ");
        $stmt->execute([$id_participante, $id_quiz, $id_partida, $id_pergunta, $id_alternativa, $correta]);
    }

    echo json_encode([
        'success' => true,
        'id_partida' => $id_partida
    ]);

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
