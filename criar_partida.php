<?php
session_start();
require_once 'conexao.php';

$id_usuario = $_SESSION['usuario']['id'];

// cria a partida
$stmt = $pdo->prepare("INSERT INTO Partidas_TBL (tipo, duracao, data, status) VALUES (?, ?, ?, ?)");
$stmt->execute(['solo', 0, date('Y-m-d'), 'andamento']);
$id_partida = $pdo->lastInsertId();

// cria o participante vinculado ao usuário
$stmt = $pdo->prepare("INSERT INTO Participantes_TBL (id_usuario, id_partida, pontuacao) VALUES (?, ?, 0)");
$stmt->execute([$_SESSION['usuario']['id'], $id_partida]);
$id_participante = $pdo->lastInsertId();

// salva na sessão
$_SESSION['id_partida'] = $id_partida;
$_SESSION['id_participante'] = $id_participante;

// redireciona
header("Location: jogar.php");
exit;
 