<?php
session_start();
require_once "conexao.php";

$erro = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $senha = $_POST['senha'];

    if (!empty($email) && !empty($senha)) {
        try {
            $stmt = $pdo->prepare("SELECT id, nome, nivel, senha, sexo FROM usuarios WHERE email = :email");
            $stmt->execute([':email' => $email]);
            // ... após buscar o usuário no banco ...
// ... após buscar o usuário no banco ...
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $senha_digitada = trim($_POST['senha']);
    
    // Testa comparação direta primeiro
    if ($senha_digitada === '123456') {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        $_SESSION['usuario_nivel'] = $usuario['nivel'];
        header("Location: dashboard.php");
        exit();
    } else {
        die("Falha: A senha digitada [$senha_digitada] não é '123456'.");
    }
} else {
    die("E-mail não encontrado.");
}
        } catch (PDOException $e) {
            $erro = "Erro no sistema: " . $e->getMessage();
        }
    } else {
        $erro = "Por favor, preencha todos os campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .input-box { 
            width: 100%; 
            background: rgba(255,255,255,0.05); 
            border: 1px solid rgba(255,255,255,0.1); 
            padding: 12px; 
            border-radius: 8px; 
            color: white; 
            margin-bottom: 15px; 
            outline: none; 
            transition: border-color 0.2s;
        }
        
        .input-box:focus {
            border-color: rgba(255, 255, 255, 0.4);
        }

        .btn-login {
            width: 100%;
            padding: 12px;
            background: rgba(255, 255, 255, 0.03); 
            color: #FFD700; 
            border: 1px solid rgba(255, 215, 0, 0.3); 
            border-radius: 8px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background: rgba(255, 215, 0, 0.1); 
            border-color: #FFD700; 
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.2); 
        }

        /* Área de cadastro ajustada para ficar em linha única */
        .secao-registrar {
            margin-top: 20px;
            padding-top: 15px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            font-size: 14px;
            color: #888;
            text-align: center;
        }

        .secao-registrar a {
            color: #ccc;
            text-decoration: none;
            font-weight: bold;
            margin-left: 5px; /* Cria um espaçamento sutil após o texto */
            transition: all 0.2s ease;
        }

        .secao-registrar a:hover {
            color: #FFD700;
        }

        .links-uteis { 
            display: flex; 
            justify-content: space-between; 
            margin-top: 20px; 
            font-size: 12px; 
            gap: 10px;
        }

        .links-uteis a { 
            color: #666; 
            text-decoration: none; 
            transition: color 0.2s; 
        }

        .links-uteis a:hover { 
            color: #FFD700; 
        }

        .alerta-erro { 
            background: rgba(255, 71, 87, 0.15); 
            color: #ff4757; 
            border: 1px solid #ff4757; 
            padding: 10px; 
            border-radius: 8px; 
            font-size: 14px; 
            margin-bottom: 15px; 
            text-align: center; 
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15);">
    <img src="assets/logo.png" class="logo">
    <h1>Cacatua Training</h1>
    <p class="subtitle">Faça login para acessar seus treinos</p>

    <?php if (!empty($erro)): ?>
        <div class="alerta-erro">
            <i class="fa-solid fa-circle-exclamation"></i> <?php echo $erro; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <input type="email" name="email" class="input-box" placeholder="Seu E-mail" required>
        <input type="password" name="senha" class="input-box" placeholder="Sua Senha" required>
        <button type="submit" class="btn-login">Entrar no Sistema</button>
    </form>

    <!-- TEXTO E LINK EM LINHA ÚNICA CORRIGIDOS -->
    <div class="secao-registrar">
        Novo por aqui? <a href="cadastrar.php">Crie sua conta.</a>
    </div>

    <div class="links-uteis">
        <a href="esqueceu_senha.php"><i class="fa-solid fa-key"></i> Senha</a>
        <a href="planos.php"><i class="fa-solid fa-gem"></i> Planos</a>
        <a href="suporte.php"><i class="fa-solid fa-headset"></i> Suporte</a>
    </div>
</div>

</body>
</html>