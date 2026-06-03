<?php
session_start();
require_once "conexao.php";

try {
    // Busca todos os planos ordenados pelo preço
    $stmt = $pdo->query("SELECT * FROM planos ORDER BY preco ASC");
    $planos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planos = [];
    $erro = "Erro ao carregar planos: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossos Planos - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        /* Padronização visual conforme discutido */
        .plan-card {
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,215,0,0.2);
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 20px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .plan-card:hover { transform: scale(1.02); border-color: #FFD700; }
        .price { font-size: 28px; color: #FFD700; font-weight: bold; margin: 15px 0; }
        .btn-plan { 
            display: block; width: 100%; padding: 12px; background: #FFD700; 
            color: #000; border: none; border-radius: 8px; font-weight: bold; 
            cursor: pointer; text-decoration: none; margin-top: 15px; 
        }
        .vantagens { font-size: 14px; color: #ccc; margin-bottom: 10px; line-height: 1.6; }
    </style>
</head>
<body>

<div class="container">
    <img src="assets/logo.png" class="logo">
    <h1 style="color: #fff; font-size: 22px; margin-bottom: 25px;">Escolha seu Plano</h1>

    <?php if (isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>

    <?php foreach ($planos as $plano): ?>
        <div class="plan-card">
            <h2 style="color: #fff;"><?php echo htmlspecialchars($plano['nome']); ?></h2>
            <div class="price">R$ <?php echo number_format($plano['preco'], 2, ',', '.'); ?></div>
            <p class="vantagens"><?php echo nl2br(htmlspecialchars($plano['vantagens'])); ?></p>
            
            <form action="contratar_plano.php" method="GET">
                <input type="hidden" name="plano" value="<?php echo htmlspecialchars($plano['nome']); ?>">
                <button type="submit" class="btn-plan">Assinar Agora</button>
            </form>
        </div>
    <?php endforeach; ?>

    <a href="dashboard.php" style="color: #666; display: block; margin-top: 20px;">Voltar</a>
</div>

</body>
</html>