<?php
session_start();
require '../conexao.php';

$id_usuario = $_SESSION['usuario']['id'];

// 1. Verifica se já existe partida esperando um jogador
$stmt = $pdo->prepare("SELECT * FROM Partidas_TBL WHERE tipo='multiplayer' AND status='em andamento' AND id_participante2 IS NULL LIMIT 1");
$stmt->execute();
$partida = $stmt->fetch(PDO::FETCH_ASSOC);

if ($partida) {
    // 2a. Já existe uma partida esperando → cria participante vinculado a ela
    $stmt = $pdo->prepare("INSERT INTO Participantes_TBL (id_usuario, id_partida, pontuacao) VALUES (?, ?, 0)");
    $stmt->execute([$id_usuario, $partida['id_partida']]);
    $id_participante = $pdo->lastInsertId();

    // salva na sessão
    $_SESSION['id_participante'] = $id_participante;

    // Atualiza a partida, setando o jogador2
    $stmt = $pdo->prepare("UPDATE Partidas_TBL SET id_participante2 = ? WHERE id_partida = ?");
    $stmt->execute([$id_participante, $partida['id_partida']]);

} else {
    // 2b. Não existe partida esperando → cria uma nova
    // primeiro insere o participante sem partida (ainda)
    $stmt = $pdo->prepare("INSERT INTO Participantes_TBL (id_usuario, id_partida, pontuacao) VALUES (?, NULL, 0)");
    $stmt->execute([$id_usuario]);
    $id_participante = $pdo->lastInsertId();

    // salva na sessão
    $_SESSION['id_participante'] = $id_participante;

    // cria a nova partida e vincula esse participante como jogador1
    $stmt = $pdo->prepare("INSERT INTO Partidas_TBL (tipo, status, id_participante1, data, duracao) VALUES ('multiplayer','em andamento',?,NOW(), 0)");
    $stmt->execute([$id_participante]);
    $id_nova_partida = $pdo->lastInsertId();

    // atualiza o participante com o id da nova partida
    $stmt = $pdo->prepare("UPDATE Participantes_TBL SET id_partida = ? WHERE id_participante = ?");
    $stmt->execute([$id_nova_partida, $id_participante]);

    // define $partida pra redirecionamento
    $partida = ['id_partida' => $id_nova_partida];
}

// redireciona para tela de espera
header("Location: espera_competitivo.php?id_partida=" . $partida['id_partida']);
exit;
?>
