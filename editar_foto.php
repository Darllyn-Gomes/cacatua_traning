<?php
session_start();
require_once "conexao.php";

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

// Lógica de Processamento (Upload ou Escolha de Cacatua)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_SESSION['usuario_id'];
    $nomeArquivo = "";

    // 1. Se o usuário clicou em uma das Cacatuas (Botão)
    if (isset($_POST['cacatua_escolhida'])) {
        $nomeArquivo = $_POST['cacatua_escolhida'];
    } 
    // 2. Se o usuário subiu uma foto do próprio computador
    elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $pasta = "uploads/";
        if (!is_dir($pasta)) { mkdir($pasta, 0755, true); }
        
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = "foto_" . $id . "." . $ext;
        
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $pasta . $nomeArquivo)) {
            // Sucesso no upload
        } else {
            $erro = "Erro ao mover o arquivo. Verifique as permissões da pasta.";
        }
    }

    // Se houve sucesso na definição do arquivo, atualiza o banco
    if ($nomeArquivo != "") {
        $pdo->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?")->execute([$nomeArquivo, $id]);
        header("Location: dashboard.php");
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personalizar Perfil</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 400px; margin: 40px auto; padding: 30px; color: #fff; background: rgba(255, 255, 255, 0.03); border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.1); }
        
        h2 { margin-bottom: 25px; font-size: 22px; color: #fff; text-align: center; }
        
        .form-group { margin-bottom: 25px; }
        
        label { display: block; margin-bottom: 12px; color: #ccc; font-size: 14px; }
        
        input[type="file"] { margin-bottom: 15px; width: 100%; color: #fff; }
        
        button { 
            padding: 10px 20px; border-radius: 8px; border: none; background: #FFD700; 
            color: #000; font-weight: bold; cursor: pointer; transition: 0.3s;
        }
        button:hover { opacity: 0.9; }

        .cacatuas-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 15px; margin-top: 15px; }
        .btn-cacatua { border: 2px solid transparent; background: none; cursor: pointer; padding: 0; transition: 0.2s; }
        .btn-cacatua:hover { border-color: #FFD700; border-radius: 50%; transform: scale(1.05); }
        
        hr { border: 0; border-top: 1px solid rgba(255,255,255,0.1); margin: 30px 0; }
        
        .link-voltar { color: #888; text-decoration: none; transition: 0.3s; display: inline-block; }
        .link-voltar:hover { color: #FFD700; text-shadow: 0 0 8px rgba(255, 215, 0, 0.4); }
    </style>
</head>
<body>
<div class="container">
    <h2>Personalizar Perfil</h2>
    
    <?php if(isset($erro)) echo "<p style='color:red;'>$erro</p>"; ?>

    <form action="editar_foto.php" method="POST" enctype="multipart/form-data">
        <div class="form-group">
            <label>Subir foto do seu dispositivo:</label>
            <input type="file" name="foto" accept="image/*">
            <button type="submit">Salvar Foto</button>
        </div>
    </form>

    <hr>

    <form action="editar_foto.php" method="POST">
        <label>Ou escolha uma das nossas Cacatuas:</label>
        <div class="cacatuas-grid">
            <?php for($i = 1; $i <= 5; $i++): ?>
                <button type="submit" name="cacatua_escolhida" value="cacatua<?php echo $i; ?>.png" class="btn-cacatua">
                    <img src="assets/cacatua<?php echo $i; ?>.png" style="width:70px; height:70px; border-radius:50%;">
                </button>
            <?php endfor; ?>
        </div>
    </form>
    
    <br><br>
    <a href="dashboard.php" class="link-voltar">← Voltar</a>
</div>
</body>
</html>