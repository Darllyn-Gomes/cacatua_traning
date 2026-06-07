<?php
session_start();
require_once "conexao.php";

// TRAVA DE SEGURANÇA
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'adm') {
    header("Location: dashboard.php");
    exit;
}

$mensagem = "";
$tipo_alerta = "sucesso";

// PROCESSAMENTO
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['acao'])) {
        $id_usuario = intval($_POST['id_usuario']);
        
        if ($id_usuario === intval($_SESSION['usuario_id'])) {
            $mensagem = "Erro: Você não pode alterar suas próprias permissões.";
            $tipo_alerta = "erro";
        } else {
            try {
                if ($_POST['acao'] === 'alterar_nivel') {
                    $novo_nivel = $_POST['nivel'];
                    $stmt = $pdo->prepare("UPDATE usuarios SET nivel = :nivel WHERE id = :id");
                    $stmt->execute([
                        ':nivel' => $novo_nivel, 
                        ':id'    => $id_usuario
                    ]);
                    $mensagem = "Nível atualizado com sucesso!";
                } elseif ($_POST['acao'] === 'excluir') {
                    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
                    $stmt->execute([':id' => $id_usuario]);
                    $mensagem = "Usuário removido com sucesso.";
                }
            } catch (PDOException $e) {
                $mensagem = "Erro: " . $e->getMessage();
                $tipo_alerta = "erro";
            }
        }
    }
}

$stmt = $pdo->prepare("SELECT id, nome, email, nivel, sexo FROM usuarios WHERE id != :id_atual ORDER BY nome ASC");
$stmt->execute([':id_atual' => $_SESSION['usuario_id']]);
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acessos - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>

        .container { max-width: 400px; margin: 0 auto; padding: 20px; }
        .card-usuario { background: #1a1a1a; border: 1px solid #333; border-radius: 10px; padding: 16px; margin-bottom: 12px; transition: 0.3s; }
        .card-usuario:hover { border-color: #444; background: #1f1f1f; }
        /* Mantendo o resto igual... */
    .controles-container { display: flex; gap: 10px; align-items: center; margin-top: 12px; }
       .input-app { flex: 1; height: 42px; background: #121212 !important; border: 1px solid #333 !important; color: #ddd !important; border-radius: 6px; padding: 0 12px; cursor: pointer; }
        .input-app:hover { border-color: #FFD700 !important; }

        /* Texto "Gerencie permissões" mais claro e maior */
    .subtitle-custom { 
        color: #aaa !important; 
        font-size: 15px !important; 
        margin-bottom: 25px !important; 
    }
        .btn-deletar-mobile {
        width: 42px;
        height: 42px;
        flex-shrink: 0; /* Garante que ele não fique "torto" ou achatado */
        background: #1a1a1a;
        border: 1px solid #333;
        color: #ff4757; /* Vermelho forte na lixeira */
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
    }
        .btn-deletar-mobile:hover {
        background: #ff4757; /* Fundo vermelho forte no hover */
        color: #fff;        /* Ícone branco no hover */
        border-color: #ff4757;
    }
        .btn-voltar { color: #888; text-decoration: none; font-size: 14px; transition: 0.3s; display: inline-block; margin-top: 20px; }
        .btn-voltar:hover { color: #FFD700; }
        .alerta { padding: 12px; border-radius: 8px; font-size: 13px; margin-bottom: 15px; text-align: center; font-weight: bold; }
        .alerta-sucesso { background: rgba(46, 213, 115, 0.1); color: #2ed573; border: 1px solid #2ed573; }
        .alerta-erro { background: rgba(255, 71, 87, 0.1); color: #ff4757; border: 1px solid #ff4757; }
    </style>
</head>
<body>

<div class="container">
    <h1>Usuários</h1>
    <!-- Subtítulo mais claro e maior -->
    <p class="subtitle-custom">Gerencie permissões de professores e alunos</p>

    <!-- ... restante do seu código ... -->

    <?php if (!empty($mensagem)): ?>
        <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <div class="lista-usuarios">
        <?php foreach ($usuarios as $usr): ?>
            <div class="card-usuario">
                <h3 style="margin: 0; font-size: 16px; color: #fff;"><?php echo htmlspecialchars($usr['nome']); ?></h3>
                <p style="margin: 2px 0 0 0; font-size: 12px; color: #777;"><?php echo htmlspecialchars($usr['email']); ?></p>

                <form method="POST" action="" class="controles-container">
                    <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                    <input type="hidden" name="acao" value="alterar_nivel">
                    
                    <select name="nivel" class="input-app" onchange="this.form.submit()">
                        <option value="aluno" <?php echo $usr['nivel'] === 'aluno' ? 'selected' : ''; ?>>Aluno</option>
                        <option value="professor" <?php echo $usr['nivel'] === 'professor' ? 'selected' : ''; ?>>Professor</option>
                        <option value="adm" <?php echo $usr['nivel'] === 'adm' ? 'selected' : ''; ?>>Admin</option>
                    </select>

                    <button type="submit" name="acao" value="excluir" class="btn-deletar-mobile" title="Excluir" onclick="return confirm('Excluir este usuário?');">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </form>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="text-align: center;">
        <a href="dashboard.php" class="btn-voltar">&larr; Voltar ao Painel</a>
    </div>
</div>

</body>
</html>