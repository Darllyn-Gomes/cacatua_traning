<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Suporte Técnico - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .opcoes-suporte {
            display: flex;
            flex-direction: column;
            gap: 15px;
            margin-top: 25px;
        }
        .btn-suporte {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 16px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.2s ease;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        /* Botão específico do WhatsApp */
        .btn-wpp {
            background: rgba(37, 211, 102, 0.1);
            color: #25D366;
            border-color: rgba(37, 211, 102, 0.3);
        }
        .btn-wpp:hover {
            background: #25D366;
            color: #111;
            box-shadow: 0 0 15px rgba(37, 211, 102, 0.4);
        }
        /* Botão específico do E-mail */
        .btn-email {
            background: rgba(0, 173, 181, 0.1);
            color: #00adb5;
            border-color: rgba(0, 173, 181, 0.3);
        }
        .btn-email:hover {
            background: #00adb5;
            color: #111;
            box-shadow: 0 0 15px rgba(0, 173, 181, 0.4);
        }
        .btn-suporte i {
            font-size: 20px;
        }
    </style>
</head>
<body>

<!-- CONTAINER CORRIGIDO COM BORDA NEUTRA -->
<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15);">
    <img src="assets/logo.png" class="logo">
    
    <h1>Suporte Técnico</h1>
    <!-- ... restante do conteúdo continua igual ... -->
    <p class="subtitle">Precisa de ajuda com o sistema? Escolha um canal para atendimento imediato:</p>

    <div class="opcoes-suporte">
        <!-- LINK ATIVO PARA O WHATSAPP -->
        <!-- O 'target="_blank"' serve para abrir em uma nova aba sem fechar o seu sistema -->
        <a href="https://wa.me/5581999999999?text=Olá,%20estou%20com%20problemas%20para%20acessar%20o%20Cacatua%20Training." 
           target="_blank" 
           class="btn-suporte btn-wpp">
            <i class="fa-brands fa-whatsapp"></i> Chamar no WhatsApp
        </a>

        <!-- LINK ATIVO PARA O E-MAIL -->
        <a href="mailto:suporte@cacatuatraining.com?subject=Suporte%20Cacatua%20Training&body=Olá,%20estou%20enfrentando%20o%20seguinte%20problema:" 
           class="btn-suporte btn-email">
            <i class="fa-solid fa-envelope"></i> Enviar um E-mail
        </a>
    </div>

    <p style="font-size: 11px; color: #666; margin-top: 25px;">
        Atendimento técnico de Segunda a Sexta, das 06h às 22h.
    </p>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.05); margin: 20px 0;">

    <a href="index.php" style="display: inline-block;"><i class="fa-solid fa-arrow-left"></i> Voltar para o Login</a>
</div>

</body>
</html>