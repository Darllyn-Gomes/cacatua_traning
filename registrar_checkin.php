<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$aluno_id = $_SESSION['usuario_id'];

// Verifica se JÁ existe um registro hoje ANTES de inserir
$stmt = $pdo->prepare("SELECT id FROM checkins 
                       WHERE aluno_id = ? 
                       AND DATE(data_checkin) = CURDATE()");
$stmt->execute([$aluno_id]);

if ($stmt->rowCount() == 0) {
    // Só insere se não encontrar nenhum registro hoje
    $insert = $pdo->prepare("INSERT INTO checkins (aluno_id) VALUES (?)");
    $insert->execute([$aluno_id]);
    $status = "sucesso";
} else {
    // Já existe, não faz nada e avisa
    $status = "ja_registrado";
}

header("Location: dashboard.php?checkin=" . $status);
exit;
?>