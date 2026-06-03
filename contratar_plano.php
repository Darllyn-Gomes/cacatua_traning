<?php
session_start();
$plano = $_GET['plano'] ?? 'Mensal';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        /* FORÇANDO O PADRÃO VISUAL AQUI DENTRO */
        
        /* Ajusta o container para ficar igual ao seu Login */
        .container {
            max-width: 420px !important; /* O !important ignora o estilo externo */
            margin: 50px auto !important;
        }

        /* Ajusta a logo especificamente nesta página */
        .logo {
            width: 150px !important; /* Mude este valor se quiser a logo maior ou menor */
            height: auto !important;
            margin: 0 auto 20px auto !important;
        }

        /* Estilo das opções de pagamento */
        .radio-group { margin: 20px 0; text-align: left; }
        .radio-group label { 
            display: block; 
            background: #222; 
            padding: 15px; 
            margin-bottom: 10px; 
            border-radius: 12px; 
            cursor: pointer; 
            color: #fff;
            border: 1px solid rgba(255,255,255,0.1);
        }

        /* Estilo da caixa de feedback */
        #feedback-pagamento { 
            margin-top: 20px; 
            padding: 15px; 
            background: #222; 
            border-radius: 12px; 
            color: #ccc; 
            font-size: 14px; 
            border: 1px solid #333;
        }
        
        .qrcode-img { width: 150px; height: 150px; margin: 10px auto; display: block; border: 2px solid #fff; }
    </style>
</head>
<body>

<div class="container">
    <img src="assets/logo.png" class="logo" alt="Logo">
    
    <h1 style="font-size: 24px; color: #FFD700; margin-bottom: 10px;">Pagamento</h1>
    <p class="subtitle">Plano selecionado: <?php echo htmlspecialchars($plano); ?></p>
    
    <form method="POST" action="gerar_cupom.php" onchange="atualizarPagamento()">
        <input type="hidden" name="plano" value="<?php echo htmlspecialchars($plano); ?>">
        
        <div class="radio-group">
            <label><input type="radio" name="metodo" value="Pix" checked> <i class="fa-brands fa-pix"></i> PIX</label>
            <label><input type="radio" name="metodo" value="Cartão"> <i class="fa-solid fa-credit-card"></i> Cartão</label>
            <label><input type="radio" name="metodo" value="Dinheiro"> <i class="fa-solid fa-money-bill-wave"></i> Dinheiro</label>
        </div>

        <div id="feedback-pagamento"></div>
        
        <button type="submit">Confirmar Pagamento</button>
    </form>

    <a href="planos.php">Voltar</a>
</div>

<script>
function atualizarPagamento() {
    const metodo = document.querySelector('input[name="metodo"]:checked').value;
    const box = document.getElementById('feedback-pagamento');
    
    if(metodo === 'Pix') {
        box.innerHTML = '<strong>Escaneie o QR Code para pagar:</strong><br><img src="assets/qr.png" class="qrcode-img">';
    } else if(metodo === 'Cartão') {
        box.innerHTML = '<strong>Pagamento Presencial:</strong><br>Favor dirigir-se à recepção para processar a transação via terminal de cartão.';
    } else {
        box.innerHTML = '<strong>Pagamento em Espécie:</strong><br>Favor dirigir-se ao setor financeiro para efetuar o pagamento e receber seu recibo.';
    }
}
atualizarPagamento();
</script>

</body>
</html>