<?php

$host = "shinkansen.proxy.rlwy.net:23072";
$dbname = "railway";
$username = "root";
$password = "nQXnKkcsnWRxwrdgWerTLsvcLfpIgOgh";
$port = "3306";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8");
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}
?>