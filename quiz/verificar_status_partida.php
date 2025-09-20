<?php
session_start();
require '../conexao.php';

$id_partida = $_GET['id_partida'];
$id_participante = $_SESSION['id_participante'];

$stmt = $pdo->prepare("SELECT * FROM Partidas_TBL WHERE id_partida = ?");
$stmt->execute([$id_partida]);
$partida = $stmt->fetch();

$outro_jogador = ($id_participante == $partida['id_participante1']) ? $partida['id_participante2'] : $partida['id_participante1'];

echo json_encode([
    'status' => $partida['status'],
    'outro_jogador' => $outro_jogador,
    'pontuacao1' => $partida['pontuacao1'],
    'pontuacao2' => $partida['pontuacao2']
]);
?>