<?php
session_start();
require_once 'conexao.php';

$id_usuario = $_SESSION['usuario']['id'] ?? null;
$novo_nome = $_POST['nome'] ?? null;

if (!$id_usuario || !$novo_nome) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

try {
    $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET nome = ? WHERE id_usuario = ?");
    $stmt->execute([$novo_nome, $id_usuario]);

    echo json_encode(['success' => true, 'nome' => htmlspecialchars($novo_nome)]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar nome.']);
}
