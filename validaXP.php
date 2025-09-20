<?php
// validaXP.php
$id_usuario = $_SESSION['usuario']['id'] ?? null;

if ($id_usuario) {
    $xp_necessario = 500;

    $stmt = $pdo->prepare("SELECT experiencia, nivel FROM Usuarios_TBL WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($dados) {
        $xp_atual = (int)$dados['experiencia'];
        $nivel_atual = (int)$dados['nivel'];

        while ($xp_atual >= $xp_necessario) {
            $nivel_atual++;
            $xp_atual -= $xp_necessario;
        }

        $stmt = $pdo->prepare("UPDATE Usuarios_TBL SET experiencia = ?, nivel = ? WHERE id_usuario = ?");
        $stmt->execute([$xp_atual, $nivel_atual, $id_usuario]);
    }
}
