<?php
session_start();
require_once 'conexao.php';

// Habilita exceptions do PDO (se ainda não estiver)
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$id_participante = $_SESSION['id_participante'] ?? null;
if (!$id_participante) {
    http_response_code(400);
    die("Erro: participante não encontrado na sessão!");
}

$id_exercicio = $_POST['id_exercicio'] ?? null;
$id_alternativa = $_POST['resposta'] ?? null;
if (!$id_exercicio || !$id_alternativa) {
    http_response_code(400);
    die("Erro: dados incompletos.");
}

try {
    $pdo->beginTransaction();

    // pega info do exercício
    $stmt = $pdo->prepare("SELECT tipo, explicacao, url_recurso FROM Exercicios_TBL WHERE id_exercicio = ?");
    $stmt->execute([$id_exercicio]);
    $exercicio = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$exercicio) {
        throw new Exception("Exercício não encontrado: $id_exercicio");
    }

    // pega a alternativa escolhida
    $stmt = $pdo->prepare("SELECT correta FROM Alternativas_TBL WHERE id_alternativa = ?");
    $stmt->execute([$id_alternativa]);
    $alt = $stmt->fetch(PDO::FETCH_ASSOC);

    $acertou = ($alt && (int)$alt['correta'] === 1) ? 1 : 0;

    $id_quiz = $_SESSION['id_quiz'] ?? null;
    $id_partida = $_SESSION['id_partida'] ?? null;

    $stmt = $pdo->prepare("
        INSERT INTO Respostas_partida_TBL 
        (tempo_resposta, correta, id_exercicio, id_participante, id_alternativa, id_quiz, id_partida)
        VALUES (0, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->execute([$acertou, $id_exercicio, $id_participante, $id_alternativa, $id_quiz, $id_partida]);


    // mapa de pontos e xp (compatível com PHP < 8)
    $pontos_map = ['facil' => 5, 'medio' => 10, 'dificil' => 20];
    $xp_map     = ['facil' => 8, 'medio' => 12, 'dificil' => 25];

    $tipo = $exercicio['tipo'] ?? '';
    $pontosPorAcerto = $pontos_map[$tipo] ?? 0;
    $xpGanho = $xp_map[$tipo] ?? 5;

    if ($acertou) {
        // atualiza pontuação e experiencia (COALESCE para evitar NULL)
        $stmt = $pdo->prepare("
            UPDATE Participantes_TBL
            SET pontuacao = COALESCE(pontuacao,0) + ?, 
                experiencia = COALESCE(experiencia,0) + ?
            WHERE id_participante = ?
        ");
        $stmt->execute([$pontosPorAcerto, $xpGanho, $id_participante]);

        // se nenhuma linha foi atualizada, algo está errado (id inválido ou coluna ausente)
        if ($stmt->rowCount() === 0) {
            throw new Exception("UPDATE não afetou linhas — verifique se id_participante existe e se a coluna 'experiencia' existe.");
        }
    }

    $pdo->commit();

    // avança e guarda feedback pra mostrar
    $_SESSION['atual']++;
    $_SESSION['feedback'] = [
        'acertou'     => $acertou,
        'explicacao'  => $exercicio['explicacao'],
        'url_recurso' => $exercicio['url_recurso']
    ];

    header("Location: feedback.php");
    exit;

} catch (Exception $e) {
    // rollback, log e redireciona para feedback com mensagem amigável
    if ($pdo->inTransaction()) $pdo->rollBack();

    error_log("[responder.php] erro: " . $e->getMessage());

    // opcional: mostra mensagem curta para o usuário (não detalhar o erro)
    $_SESSION['feedback'] = [
        'acertou' => $acertou ?? 0,
        'explicacao' => $exercicio['explicacao'] ?? '',
        'url_recurso' => $exercicio['url_recurso'] ?? ''
    ];
    $_SESSION['error_msg'] = "Ocorreu um erro ao registrar sua resposta. O administrador foi notificado.";

    header("Location: feedback.php");
    exit;
}
