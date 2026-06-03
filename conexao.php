<?php
// Especificamos a porta 3307 que está ativa no seu XAMPP
$host = "127.0.0.1:3307"; 
$usuario = "root";
$senha = "";
$banco = "cacatuaa_training"; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
?>