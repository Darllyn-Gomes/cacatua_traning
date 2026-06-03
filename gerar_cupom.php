<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] !== "POST") { header("Location: cadastrar.php"); exit(); }

$plano = $_POST['plano'];
$metodo = $_POST['metodo'];
$data = date('d/m/Y H:i');
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recibo - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        /* Reutilizando o container padrão do sistema */
        .container { 
            max-width: 400px; margin: 50px auto; padding: 30px; 
            border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 12px; 
            text-align: center; background: rgba(0,0,0,0.8);
        }
        
        .cupom-visual {
            background: #fff; color: #000; padding: 25px; border-radius: 8px;
            text-align: left; font-family: 'Courier New', Courier, monospace;
            margin-bottom: 20px; border: 1px solid #ddd;
        }
        
        .cupom-visual h3 { text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px; }
        .cupom-visual p { margin: 5px 0; font-size: 14px; display: flex; justify-content: space-between; }
        
        .btn-imprimir { 
            width: 100%; padding: 12px; background: #FFD700; color: #000; 
            border: none; border-radius: 8px; font-weight: bold; cursor: pointer; 
        }

        /* Esconde o que não é o cupom na hora de imprimir */
        @media print {
            body * { visibility: hidden; }
            .cupom-visual, .cupom-visual * { visibility: visible; }
            .cupom-visual { position: absolute; left: 0; top: 0; width: 100%; border: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <img src="assets/logo.png" style="width: 100px; margin-bottom: 15px;">
    
    <div class="cupom-visual">
        <h3>CACATUA TRAINING</h3>
        <p><span>Data:</span> <strong><?php echo $data; ?></strong></p>
        <p><span>Plano:</span> <strong><?php echo htmlspecialchars($plano); ?></strong></p>
        <p><span>Método:</span> <strong><?php echo htmlspecialchars($metodo); ?></strong></p>
        <p><span>Status:</span> <strong>CONFIRMADO</strong></p>
        <hr style="border: 0; border-top: 1px dashed #000; margin: 15px 0;">
        <p style="text-align: center; font-size: 12px;">Agradecemos a preferência!</p>
    </div>

    <button class="btn-imprimir no-print" onclick="window.print()">Imprimir Comprovante</button>
    <a href="dashboard.php" class="no-print" style="color: #666; font-size: 14px; margin-top: 20px; display: block;">Ir para Dashboard</a>
</div>

</body>
</html>