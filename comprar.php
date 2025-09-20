<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];
$id_produto = intval($_POST['id_produto'] ?? 0);

if ($id_produto <= 0) {
    header("Location: loja.php?msg=Produto inválido!");
    exit;
}

try {
    $pdo->beginTransaction();

    // pega info do produto
    $stmt = $pdo->prepare("SELECT preco FROM Produtos_TBL WHERE id_produto = ?");
    $stmt->execute([$id_produto]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        throw new Exception("Produto não encontrado!");
    }

    // bloqueia linha do usuário (evita race condition)
    $stmt = $pdo->prepare("SELECT pontos FROM Usuarios_TBL WHERE id_usuario = ? FOR UPDATE");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        throw new Exception("Usuário inválido!");
    }

    if ($usuario['pontos'] < $produto['preco']) {
        throw new Exception("Você não tem pontos suficientes!"); 
    }

    // desconta pontos
    $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET pontos = pontos - ? WHERE id_usuario = ?");
    $stmt->execute([$produto['preco'], $id_usuario]);

    // insere item no inventário
    $stmt = $pdo->prepare("INSERT INTO Itens_TBL (data_aquisicao, id_usuario, id_produto) VALUES (CURDATE(), ?, ?)");
    $stmt->execute([$id_usuario, $id_produto]);

    $pdo->commit();

    header("Location: loja.php?msg=Compra realizada com sucesso!");
    exit;
} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: loja.php?msg=" . urlencode($e->getMessage()));
    exit;
}
