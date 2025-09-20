<?php
session_start();
require '../conexao.php';

try {
    // ativa exceções do PDO (útil para debug)
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (Exception $e) {
    // se a conexão já tiver sido configurada, ignora
}

$id_partida = $_GET['id_partida'] ?? null;
$id_participante = $_SESSION['id_participante'] ?? null;
$debug = isset($_GET['debug']) && $_GET['debug'] == '1';

if (!$id_partida) {
    if ($debug) { echo "Erro: id_partida não fornecido."; exit; }
    die("Erro: id_partida não fornecido.");
}

try {
    // Pega dados da partida
    $stmt = $pdo->prepare("SELECT id_quiz, id_participante1, id_participante2 FROM Partidas_TBL WHERE id_partida = ?");
    $stmt->execute([$id_partida]);
    $partida = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$partida) throw new Exception("Partida não encontrada.");

    $id_quiz = $partida['id_quiz'];
    $p1 = $partida['id_participante1'];
    $p2 = $partida['id_participante2'];

    // prepare para contar acertos
    $stmtCount = $pdo->prepare("SELECT COUNT(*) FROM Respostas_partida_TBL WHERE id_quiz = ? AND id_participante = ? AND correta = 1");

    $pontos1 = 0;
    $pontos2 = 0;

    if ($p1) {
        $stmtCount->execute([$id_quiz, $p1]);
        $pontos1 = (int)$stmtCount->fetchColumn();
    }

    if ($p2) {
        $stmtCount->execute([$id_quiz, $p2]);
        $pontos2 = (int)$stmtCount->fetchColumn();
    }

    // salva/atualiza resultados_quiz (evita duplicidade)
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Resultados_quiz_TBL WHERE id_quiz = ? AND id_participante = ?");
    $stmtInsert = $pdo->prepare("
        INSERT INTO Resultados_quiz_TBL 
        (id_quiz, id_participante, pontuacao, tempo_gasto, data_realizacao, id_partida)
        VALUES (?, ?, ?, 0, NOW(), ?)
    ");
    $stmtUpdateResultado = $pdo->prepare("
        UPDATE Resultados_quiz_TBL 
        SET pontuacao = ? 
        WHERE id_quiz = ? AND id_participante = ? AND id_partida = ?
    ");

    foreach ([$p1 => $pontos1, $p2 => $pontos2] as $pid => $pts) {
        if (!$pid) continue;
        $stmtCheck->execute([$id_quiz, $pid]);
        if ((int)$stmtCheck->fetchColumn() === 0) {
            $stmtInsert->execute([$id_quiz, $pid, $pts, $id_partida]);
        } else {
            // atualiza caso o resultado já exista (mantém consistente)
            $stmtUpdateResultado->execute([$pts, $id_quiz, $pid, $id_partida]);
        }
    }

    // atualiza a partida com as duas pontuações
    $stmt = $pdo->prepare("UPDATE Partidas_TBL SET pontuacao1 = ?, pontuacao2 = ? WHERE id_partida = ?");
    $stmt->execute([$pontos1, $pontos2, $id_partida]);

    $empate = ($pontos1 == $pontos2);

    if ($debug) {
        echo "<pre>";
        echo "DEBUG finalização de partida\n";
        echo "id_partida: $id_partida\n";
        echo "id_quiz: $id_quiz\n";
        echo "id_participante (sessão): " . ($id_participante ?? 'NULL') . "\n\n";
        echo "id_participante1: " . ($p1 ?? 'NULL') . "  -> pontos1: $pontos1\n";
        echo "id_participante2: " . ($p2 ?? 'NULL') . "  -> pontos2: $pontos2\n\n";
        echo "Partida atualizada com pontuacao1 = $pontos1, pontuacao2 = $pontos2\n";
        echo "</pre>";
        exit;
    } else {
        if ($empate) {
            header("Location: resultado_partida.php?id_partida=" . $id_partida);
            exit;

        } else {
            header("Location: resultado_partida.php?id_partida=" . $id_partida);
            exit;
        }
    }

} catch (Exception $e) {
    if ($debug) {
        echo "ERRO: " . $e->getMessage();
    } else {
        // para produção, podes gravar em log em vez de mostrar
        die("Erro ao finalizar partida: " . $e->getMessage());
    }
}
?>
