<?php
session_start();
require '../conexao.php';
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "erro", "msg" => "Precisa estar logado."]);
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];

try {
    // cria a partida
    $stmt = $pdo->prepare("
        INSERT INTO Partidas_TBL (tipo, duracao, status, desempate, data) 
        VALUES ('privada', 0, 'aguardando', 0, NOW())
    ");
    $stmt->execute();
    $id_partida = $pdo->lastInsertId();

    // cria participante host
    $stmt = $pdo->prepare("
        INSERT INTO Participantes_TBL (id_partida, id_usuario, pontuacao, posicao_final, experiencia)
        VALUES (?, ?, 0, 0, 0)
    ");
    $stmt->execute([$id_partida, $id_usuario]);
    $id_participante = $pdo->lastInsertId();
    $_SESSION['id_participante'] = $pdo->lastInsertId();

    // atualiza a partida com id_participante1
    $stmt = $pdo->prepare("
        UPDATE Partidas_TBL SET id_participante1 = ? WHERE id_partida = ?
    ");
    $stmt->execute([$id_participante, $id_partida]);

    echo json_encode([
        "status" => "ok",
        "pin" => $id_partida
    ]);

} catch (Exception $e) {
    echo json_encode([
        "status" => "erro",
        "msg" => $e->getMessage()
    ]);
}
