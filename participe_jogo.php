<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cloud Challenge - Participe de um jogo</title>
    <link rel="stylesheet" href="./css/reset.css">
    <link rel="stylesheet" href="./css/menu.css">
    <link rel="stylesheet" href="./css/participe_jogo.css">
    <link rel="stylesheet" type="text/css" href="./css/dark_mode.css">
</head>
<body>
    
    <?php include 'menu_lateral.php'; ?>

    <div class="conteudo">
        <div class="top-banner">
            <button class="btn-criar" id="btnCriar">Criar Sala Privada</button>
        </div>

        <div class="area-pin">
            <h2>Digite o PIN da sala privada:</h2>
            <form action="./quiz/entrar_sala.php" method="POST">
                <input type="text" name="pin" class="input-pin" placeholder="Digite aqui..." required>
                <br>
                <button type="submit" class="btn-conectar">Conectar</button>
            </form>
            <div id="pinGerado" class="pin-gerado"></div>
        </div>
    </div>

    <script>
    document.getElementById("btnCriar").addEventListener("click", async () => {
        console.log("Botão acionado")
        let res = await fetch("./quiz/criar_sala.php");
        let data = await res.json();
        console.log(data)
        if(data.status === "ok") {
            // redireciona para tela de espera do host
            window.location.href = `./quiz/esperar_jogador.php?id_partida=${data.pin}`;
        } else {
            alert("Erro ao criar sala: " + data.msg);
        }
    });

    </script>
</body>
</html>
