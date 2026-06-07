<?php
session_start();
require_once "conexao.php";

// Acesso apenas para Professor ou ADM
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_nivel'] !== 'professor' && $_SESSION['usuario_nivel'] !== 'adm')) {
    header("Location: dashboard.php");
    exit;
}

// Busca alunos que fizeram check-in hoje (agrupado para evitar duplicidade)
$sql = "SELECT u.nome, MIN(c.data_checkin) as primeiro_checkin 
        FROM checkins c 
        JOIN usuarios u ON c.aluno_id = u.id 
        WHERE DATE(c.data_checkin) = CURDATE() 
        GROUP BY u.id 
        ORDER BY primeiro_checkin DESC";

$stmt = $pdo->query($sql);
$presencas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Presenças de Hoje</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="container" style="max-width: 400px; margin: 0 auto; padding: 20px;">
    <h1><i class="fa-solid fa-users"></i> Presenças Hoje</h1>
    
    <?php if (empty($presencas)): ?>
        <p style="color: #666; text-align: center;">Nenhum aluno registrou presença hoje.</p>
    <?php else: ?>
        <div class="lista-controle">
            <?php foreach ($presencas as $p): ?>
                <div class="card-controle" style="padding: 10px; border-bottom: 1px solid #333;">
                    <strong><?php echo htmlspecialchars($p['nome']); ?></strong><br>
                    <small style="color: #888;">Horário: <?php echo date('H:i', strtotime($p['primeiro_checkin'])); ?></small>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <br><a href="dashboard.php">Voltar</a>
</div>
</body>
</html>