<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "message" => "Usuário não autenticado"]);
    exit;
}

$id_usuario = $_SESSION['usuario']['id'];
$nova_url   = $_POST['url_imagem'] ?? null;

if (!$nova_url) {
    echo json_encode(["success" => false, "message" => "Nenhuma URL recebida"]);
    exit;
}

$stmt = $pdo->prepare("UPDATE Usuarios_TBL SET avatar_url = ? WHERE id_usuario = ?");
$success = $stmt->execute([$nova_url, $id_usuario]);

if ($success) {
    echo json_encode(["success" => true, "message" => "Avatar atualizado!", "url" => $nova_url]);
} else {
    echo json_encode(["success" => false, "message" => "Erro ao atualizar avatar"]);
}
