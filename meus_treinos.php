<?php
session_start();
require_once "conexao.php";

// SEGURANÇA: Bloqueia acesso se não for aluno
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'aluno') {
    header("Location: dashboard.php");
    exit();
}

$id_aluno = $_SESSION['usuario_id'];

// BUSCA OS TREINOS LINCADOS AO ALUNO
try {
    $stmt = $pdo->prepare("SELECT * FROM treinos WHERE aluno_id = :aluno_id ORDER BY id DESC");
    $stmt->execute([':aluno_id' => $id_aluno]);
    $treinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $erro = "Erro ao carregar treinos.";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Treinos - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        /* Mesma identidade do Montar Treino */
        .container { 
            max-width: 400px; 
            border: 1px solid rgba(255, 255, 255, 0.15); 
            box-sizing: border-box; 
            margin: 20px auto; 
            padding: 20px; 
            border-radius: 12px; 
            background: #141414;
        }
        .treino-card { 
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 215, 0, 0.2); 
            padding: 16px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
        }
        h3 { color: #FFD700; margin-top: 0; margin-bottom: 12px; border-bottom: 1px solid rgba(255,215,0,0.1); padding-bottom: 8px; }
        .lista-exercicios { color: #ccc; font-size: 14px; white-space: pre-wrap; line-height: 1.6; }
        
        /* Botão estilizado idêntico ao de "Salvar" */
        .btn-concluir {
            background: rgba(255, 215, 0, 0.1); 
            border: 1px solid #FFD700; 
            color: #FFD700; 
            padding: 14px; 
            border-radius: 8px; 
            font-size: 15px; 
            font-weight: bold; 
            cursor: pointer; 
            transition: all 0.3s; 
            width: 100%; 
            margin-top: 15px;
        }
        .btn-concluir:hover { background: #FFD700; color: #000; box-shadow: 0 0 15px rgba(255, 215, 0, 0.3); }
        .btn-concluir:disabled { background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: #666; cursor: not-allowed; }
    </style>
</head>
<body>

<div class="container">
    <img src="assets/logo.png" class="logo">
    <h1 style="text-align: center; font-size: 24px; color: #fff;">Minha Rotina</h1>

    <?php if (empty($treinos)): ?>
        <p style="text-align:center; color: #666;">Nenhum treino encontrado.</p>
    <?php else: ?>
        <?php foreach ($treinos as $treino): ?>
            <div class="treino-card" id="card-<?php echo $treino['id']; ?>">
                <h3><i class="fa-solid fa-bolt"></i> <?php echo htmlspecialchars($treino['titulo']); ?></h3>
                <div class="lista-exercicios"><?php echo htmlspecialchars($treino['descricao']); ?></div>
                
                <button class="btn-concluir" onclick="concluirTreino(this)">
                    <i class="fa-solid fa-circle-check"></i> Marcar como Concluído
                </button>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a href="dashboard.php" style="display:block; text-align:center; color: #666; margin-top: 20px; font-size: 13px;">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
    </a>
</div>

<script>
function concluirTreino(btn) {
    btn.innerHTML = '<i class="fa-solid fa-check-double"></i> Treino Finalizado!';
    btn.disabled = true;
    btn.style.background = 'rgba(46, 213, 115, 0.1)';
    btn.style.borderColor = '#2ed573';
    btn.style.color = '#2ed573';
}
</script>

</body>
</html>