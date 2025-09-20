<?php
session_start();
require '../conexao.php';

$id_partida = $_GET['id_partida'] ?? 0;

$stmt = $pdo->prepare("SELECT id_participante2 FROM Partidas_TBL WHERE id_partida = ?");
$stmt->execute([$id_partida]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode([
    "status" => "ok",
    "temJogador2" => !empty($partida['id_participante2'])
]);
