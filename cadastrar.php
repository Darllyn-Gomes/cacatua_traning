<?php
session_start();
require_once "conexao.php";

// Busca os planos do banco de dados
try {
    $stmtPlanos = $pdo->query("SELECT nome FROM planos ORDER BY preco ASC");
    $planos = $stmtPlanos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $planos = [];
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome  = trim($_POST['nome']);
    $email = trim($_POST['email']);
    $senha = trim($_POST['senha']);
    $plano = $_POST['plano'];

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, nivel) VALUES (?, ?, ?, 'aluno')");
    
    if ($stmt->execute([$nome, $email, $senhaHash])) {
        $_SESSION['usuario_id'] = $pdo->lastInsertId();
        $_SESSION['usuario_nome'] = $nome;
        $_SESSION['usuario_nivel'] = 'aluno';
        
        header("Location: contratar_plano.php?plano=" . urlencode($plano));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        /* ESTILOS COPIADOS DO SEU INDEX PARA MANTER O PADRÃO */
        .input-box { width: 100%; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); padding: 12px; border-radius: 8px; color: white; margin-bottom: 15px; outline: none; transition: border-color 0.2s; box-sizing: border-box; }
        .input-box:focus { border-color: rgba(255, 255, 255, 0.4); }

        .btn-login { width: 100%; padding: 12px; background: rgba(255, 255, 255, 0.03); color: #FFD700; border: 1px solid rgba(255, 215, 0, 0.3); border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: all 0.3s ease; }
        .btn-login:hover { background: rgba(255, 215, 0, 0.1); border-color: #FFD700; box-shadow: 0 0 15px rgba(255, 215, 0, 0.2); }
        
        /* Ajuste do Select para combinar com os inputs */
        select.input-box { appearance: none; }
        select.input-box option { background: #222; color: #fff; }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15);">
    <img src="assets/logo.png" class="logo">
    <h1>Cacatua Training</h1>
    <p class="subtitle">Crie sua conta para começar</p>

    <form method="POST" action="cadastrar.php">
        <input type="text" name="nome" class="input-box" placeholder="Seu Nome" required>
        <input type="email" name="email" class="input-box" placeholder="Seu E-mail" required>
        <input type="password" name="senha" class="input-box" placeholder="Sua Senha" required>
        
        <select name="plano" class="input-box">
            <?php foreach ($planos as $p): ?>
                <option value="<?php echo htmlspecialchars($p['nome']); ?>">
                    <?php echo htmlspecialchars($p['nome']); ?>
                </option>
            <?php endforeach; ?>
        </select>
        
        <button type="submit" class="btn-login">Matricule-se</button>
    </form>

    <div class="secao-registrar">
        <a href="index.php">Voltar ao Login</a>
    </div>
</div>

</body>
</html>