<?php
session_start();

if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'professor') {
    header("Location: index.php");
    exit;
}

// Inicializa o histórico de medidas se não existir
if (!isset($_SESSION['historico_medidas'])) {
    $_SESSION['historico_medidas'] = [
        ['data' => '10/05/2026', 'peso' => '78kg', 'gordura' => '14%'],
    ];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_peso = $_POST['peso'] . 'kg';
    $nova_gordura = $_POST['gordura'] . '%';
    $data_atual = date('d/m/Y');

    // Insere no topo do histórico simulado
    array_unshift($_SESSION['historico_medidas'], [
        'data' => $data_atual,
        'peso' => $novo_peso,
        'gordura' => $nova_gordura
    ]);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor - Evolução Física</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .grid-inputs { display: flex; gap: 10px; margin-bottom: 15px; }
        .grid-inputs input { width: 100%; background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); padding: 12px; border-radius: 8px; color: white; text-align: center; }
        .historico { margin-top: 20px; text-align: left; }
        .linha-historico { background: rgba(255,255,255,0.02); border: 1px solid rgba(255,255,255,0.05); padding: 10px; border-radius: 6px; margin-bottom: 8px; display: flex; justify-content: space-between; font-size: 14px; }
    </style>
</head>
<body>

<div class="container" style="border: 2px solid #FFD700;">
    <img src="assets/logo.png" class="logo">

    <h1>Evolução Física</h1>
    <p class="subtitle">Aluno: Aleff Dev</p>

    <form method="POST" action="">
        <div class="grid-inputs">
            <input type="number" step="0.1" name="peso" placeholder="Peso (kg)" required>
            <input type="number" name="gordura" placeholder="BF (%)" required>
        </div>
        <button type="submit" style="background:#FFD700; color:#111; padding:10px;">Atualizar Avaliação</button>
    </form>

    <div class="historico">
        <h3 style="font-size:15px; color:#FFD700; margin-bottom:10px;"><i class="fa-solid fa-clock-rotate-left"></i> Histórico de Evolução</h3>
        <?php foreach ($_SESSION['historico_medidas'] as $registro): ?>
            <div class="linha-historico">
                <span style="color:#aaa;"><?php echo $registro['data']; ?></span>
                <strong>Peso: <?php echo $registro['peso']; ?></strong>
                <span style="color:#00adb5;">BF: <?php echo $registro['gordura']; ?></span>
            </div>
        <?php endforeach; ?>
    </div>

    <a href="dashboard.php" style="margin-top: 20px; display: inline-block;">Voltar ao Painel</a>
</div>

</body>
</html>