<?php
session_start();
require '../conexao.php';

if (!isset($_GET['id_partida'])) die("ID da partida não informado.");
$id_partida = $_GET['id_partida'];

// Pega dados da partida
$stmt = $pdo->prepare("SELECT * FROM Partidas_TBL WHERE id_partida = ?");
$stmt->execute([$id_partida]);
$partida = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$partida) die("Partida não encontrada");

// Pega participantes
$stmt = $pdo->prepare("
    SELECT p.id_participante, p.id_usuario, u.nome, u.avatar_url
    FROM Participantes_TBL p
    JOIN Usuarios_TBL u ON p.id_usuario = u.id_usuario
    WHERE p.id_participante IN (?, ?)
");
$stmt->execute([$partida['id_participante1'], $partida['id_participante2']]);
$participantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$mapParticipantes = [];
foreach ($participantes as $p) $mapParticipantes[$p['id_participante']] = $p;

$id_quiz = $partida['id_quiz'];

function calcularResultados($pdo, $id_quiz, $id_participante, $id_partida) {
    $stmt = $pdo->prepare("
        SELECT r.id_pergunta, r.correta, q.tipo
        FROM Respostas_partida_TBL r
        JOIN Perguntas_TBL q ON r.id_pergunta = q.id_pergunta
        WHERE r.id_quiz = ? AND r.id_participante = ? AND r.id_partida = ?
    ");
    $stmt->execute([$id_quiz, $id_participante, $id_partida]);
    $respostas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pontos = $xp = 0;
    $mapTipo = ['facil'=>10,'medio'=>15,'dificil'=>20];

    foreach ($respostas as $r) {
        if ($r['correta']) {
            $nivel = $mapTipo[$r['tipo']] ?? 10;
            $pontos += $nivel;
            $xp += $nivel*2;
        }
    }

    return ['pontos'=>$pontos, 'xp'=>$xp];
}

// Quem respondeu
$stmt = $pdo->prepare("SELECT DISTINCT id_participante FROM Respostas_partida_TBL WHERE id_partida = ?");
$stmt->execute([$id_partida]);
$respondidos = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Se ambos responderam e a partida não está finalizada → finaliza
if ($partida['status'] !== 'finalizada' 
    && in_array($partida['id_participante1'],$respondidos) 
    && in_array($partida['id_participante2'],$respondidos)) {

    $result1 = calcularResultados($pdo, $id_quiz, $partida['id_participante1'], $id_partida);
    $result2 = calcularResultados($pdo, $id_quiz, $partida['id_participante2'], $id_partida);

    $score_vitoria = 10;
    $delta1 = $delta2 = 0;

    $stmt = $pdo->prepare("SELECT score FROM Usuarios_TBL WHERE id_usuario=?");
    $stmt->execute([$mapParticipantes[$partida['id_participante1']]['id_usuario']]);
    $score1 = (int)$stmt->fetchColumn();

    $stmt->execute([$mapParticipantes[$partida['id_participante2']]['id_usuario']]);
    $score2 = (int)$stmt->fetchColumn();

    if ($result1['pontos'] > $result2['pontos']) {
        $vencedor = $mapParticipantes[$partida['id_participante1']]['nome'];
        $delta1 = +$score_vitoria;
        $delta2 = -$score_vitoria;
    } elseif ($result2['pontos'] > $result1['pontos']) {
        $vencedor = $mapParticipantes[$partida['id_participante2']]['nome'];
        $delta2 = +$score_vitoria;
        $delta1 = -$score_vitoria;
    } else {
        $vencedor = "Empate";
        $delta1 = $delta2 = 0;
    }

    $score1 = max(0, $score1 + $delta1);
    $score2 = max(0, $score2 + $delta2);

    // Atualiza banco só se ainda não finalizada
    $stmtUpdatePartida = $pdo->prepare("
        UPDATE Partidas_TBL 
        SET pontos1=?, pontos2=?, xp1=?, xp2=?, score1=?, score2=?, status='finalizada'
        WHERE id_partida=? AND status!='finalizada'
    ");
    $stmtUpdatePartida->execute([
        $result1['pontos'], $result2['pontos'],
        $result1['xp'], $result2['xp'],
        $score1, $score2,
        $id_partida
    ]);

    $stmtUpdateUser = $pdo->prepare("
        UPDATE Usuarios_TBL SET pontos=pontos+?, experiencia=experiencia+?, score=? WHERE id_usuario=?
    ");
    $stmtUpdateUser->execute([$result1['pontos'],$result1['xp'],$score1,$mapParticipantes[$partida['id_participante1']]['id_usuario']]);
    $stmtUpdateUser->execute([$result2['pontos'],$result2['xp'],$score2,$mapParticipantes[$partida['id_participante2']]['id_usuario']]);

    // Recarrega partida para pegar valores finais do banco
    $stmt = $pdo->prepare("SELECT * FROM Partidas_TBL WHERE id_partida = ?");
    $stmt->execute([$id_partida]);
    $partida = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Agora pega resultados consistentes
$result1 = calcularResultados($pdo, $id_quiz, $partida['id_participante1'], $id_partida);
$result2 = calcularResultados($pdo, $id_quiz, $partida['id_participante2'], $id_partida);

$vencedor = $partida['status'] === 'finalizada' ? 
    (($partida['pontos1'] > $partida['pontos2']) ? $mapParticipantes[$partida['id_participante1']]['nome'] :
    (($partida['pontos2'] > $partida['pontos1']) ? $mapParticipantes[$partida['id_participante2']]['nome'] : "Empate")) 
    : "Aguardando o outro jogador...";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
<?php if ($partida['status'] !== 'finalizada') { ?>
    <meta http-equiv="refresh" content="5">
<?php } ?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="manifest" href="/manifest.json">
<title>Resultado da Partida</title>
<link rel="stylesheet" href="../css/resultado_partida.css">
</head>
<body>
<div class="container">
    <h1>Resultado da Partida</h1>

    <div class="players">
        <?php
        // determina quem ganhou pra dar classe winner/loser
        $class1 = $class2 = "";
        if ($partida['status'] === 'finalizada' && $partida['pontos1'] != $partida['pontos2']) {
            $class1 = ($partida['pontos1'] > $partida['pontos2']) ? "winner" : "loser";
            $class2 = ($partida['pontos2'] > $partida['pontos1']) ? "winner" : "loser";
        }
        ?>

        <div class="player <?php echo $class1; ?>">
            <img class="avatar" 
            src="<?php echo htmlspecialchars(str_replace('./', '../', $mapParticipantes[$partida['id_participante1']]['avatar_url'])); ?>" 
            alt="Avatar">
            <div class="details">
                <strong><?php echo $mapParticipantes[$partida['id_participante1']]['nome']; ?></strong>
                <?php echo $result1['pontos']; ?> pontos | <?php echo $result1['xp']; ?> XP
                <div class="score">
                    <?php 
                        if ($partida['status'] !== 'finalizada') {
                            echo "⏳ Esperando outro jogador...";
                        } else {
                            echo ($partida['pontos1'] == $partida['pontos2']) ? "0 score" 
                                : (($partida['pontos1'] > $partida['pontos2']) ? "+10 score" : "-10 score");
                        }
                    ?>
                </div>
            </div>
        </div>

        <div class="player <?php echo $class2; ?>">
            <img class="avatar" 
            src="<?php echo htmlspecialchars(str_replace('./', '../', $mapParticipantes[$partida['id_participante2']]['avatar_url'])); ?>" 
            alt="Avatar">
            <div class="details">
                <strong><?php echo $mapParticipantes[$partida['id_participante2']]['nome']; ?></strong>
                <?php echo $result2['pontos']; ?> pontos | <?php echo $result2['xp']; ?> XP
                <div class="score">
                    <?php 
                        if ($partida['status'] !== 'finalizada') {
                            echo "⏳ Esperando outro jogador...";
                        } else {
                            echo ($partida['pontos1'] == $partida['pontos2']) ? "0 score" 
                                : (($partida['pontos2'] > $partida['pontos1']) ? "+10 score" : "-10 score");
                        }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="winner-title">
        🏆 Vencedor: <?php echo $vencedor; ?>
    </div>

    <a href="../index.php" class="btn">Voltar ao menu</a>
</div>

<script>
    if ("serviceWorker" in navigator) {
  window.addEventListener("load", () => {
    navigator.serviceWorker.register("../sw.js")
      .then((reg) => console.log("Service Worker registrado!", reg))
      .catch((err) => console.log("Falha ao registrar SW:", err));
  });
}
</script>
</body>
</html>

