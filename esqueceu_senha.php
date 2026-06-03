<?php
$mensagem = "";

// Verifica se o formulário foi enviado via POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    
    if (!empty($email)) {
        // Aqui no futuro faremos a checagem no banco, por enquanto damos o feedback limpo na tela
        $mensagem = "Sucesso: Instruções enviadas! Verifique sua caixa de entrada.";
    } else {
        $mensagem = "Erro: Por favor, digite um e-mail válido.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .input-box { 
            width: 100%; 
            background: rgba(255, 255, 255, 0.05); 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            padding: 12px; 
            border-radius: 8px; 
            color: white; 
            margin-bottom: 15px; 
            outline: none; 
            transition: border-color 0.2s;
        }
        .input-box:focus {
            border-color: #00adb5;
        }
        .alerta { 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 14px; 
            margin-bottom: 20px; 
            font-weight: bold; 
            text-align: center; 
        }
        .alerta-sucesso { 
            background: rgba(46, 213, 115, 0.15); 
            color: #2ed573; 
            border: 1px solid #2ed573; 
        }
        .alerta-erro { 
            background: rgba(255, 71, 87, 0.15); 
            color: #ff4757; 
            border: 1px solid #ff4757; 
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 2px solid rgba(255, 255, 255, 0.2);">
    <img src="assets/logo.png" class="logo">
    <h1>Recuperar Senha</h1>
    <p class="subtitle" style="margin-bottom: 20px;">Insira seu e-mail para receber as instruções de recuperação.</p>

    <!-- FEEDBACK INTEGRADO NA INTERFACE (Sem janelas chatas do navegador) -->
    <?php if (!empty($mensagem)): ?>
        <div class="alerta <?php echo strpos($mensagem, 'Erro') === false ? 'alerta-sucesso' : 'alerta-erro'; ?>">
            <i class="fa-solid <?php echo strpos($mensagem, 'Erro') === false ? 'fa-circle-check' : 'fa-circle-exclamation'; ?>"></i> 
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" class="input-box" placeholder="Seu e-mail cadastrado" required>
        <button type="submit" style="background: #222; border: 1px solid rgba(255,255,255,0.2); color: white; font-weight: bold;">
            <i class="fa-solid fa-paper-plane" style="margin-right: 5px;"></i> Solicitar Nova Senha
        </button>
    </form>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.05); margin: 20px 0;">

    <a href="index.php" style="display: inline-block;"><i class="fa-solid fa-arrow-left"></i> Voltar para o Login</a>
</div>

</body>
</html>