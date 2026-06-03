<?php
session_start();
require_once "conexao.php";

// TRAVA DE SEGURANÇA: Apenas Professor (ou ADM) acessa esta área
if (!isset($_SESSION['usuario_id']) || ($_SESSION['usuario_nivel'] !== 'professor' && $_SESSION['usuario_nivel'] !== 'adm')) {
    header("Location: dashboard.php");
    exit;
}

$mensagem = "";
$tipo_alerta = "sucesso";

// AÇÃO DE DELETAR UM TREINO
if (isset($_GET['excluir'])) {
    $treino_id = intval($_GET['excluir']);
    try {
        $stmt = $pdo->prepare("DELETE FROM treinos WHERE id = :id");
        $stmt->execute([':id' => $treino_id]);
        $mensagem = "Ficha de treino excluída com sucesso!";
        $tipo_alerta = "sucesso";
    } catch (PDOException $e) {
        $mensagem = "Erro ao excluir: " . $e->getMessage();
        $tipo_alerta = "erro";
    }
}

// BUSCA TODAS AS FICHAS ATIVAS NO ECOSSISTEMA DO APP
try {
    $sql = "SELECT t.id, t.titulo, t.descricao, u.nome AS aluno_nome 
            FROM treinos t 
            JOIN usuarios u ON t.aluno_id = u.id 
            ORDER BY t.id DESC";
    $stmt = $pdo->query($sql);
    $treinos_destinados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar painel de controle: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Treinos - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .lista-controle { display: flex; flex-direction: column; gap: 14px; margin-top: 20px; width: 100%; text-align: left; }
        
        .card-controle {
            background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px; padding: 16px; display: flex; flex-direction: column; gap: 8px;
        }
        .card-header { display: flex; justify-content: space-between; align-items: flex-start; }
        .aluno-info { display: flex; flex-direction: column; }
        .aluno-nome { font-size: 15px; font-weight: bold; color: #fff; }
        .treino-tag { font-size: 12px; color: #FFD700; font-weight: bold; text-transform: uppercase; margin-top: 2px; }
        
        .acoes-card { display: flex; gap: 6px; }
        .btn-acao {
            background: rgba(255, 255, 255, 0.04); border: 1px solid rgba(255, 255, 255, 0.08);
            color: #ccc; width: 34px; height: 34px; border-radius: 6px; display: flex;
            align-items: center; justify-content: center; cursor: pointer; text-decoration: none; transition: all 0.2s;
        }
        .btn-acao:hover { border-color: #FFD700; color: #FFD700; background: rgba(255, 215, 0, 0.04); }
        .btn-clonar:hover { border-color: #00d2d3; color: #00d2d3; background: rgba(0, 210, 211, 0.05); }
        .btn-excluir:hover { border-color: #ff4757; color: #ff4757; background: rgba(255, 71, 87, 0.05); }

        .detalhes-exercicios {
            display: none; background: rgba(0, 0, 0, 0.2); 
            border-radius: 6px; padding: 10px; margin-top: 8px;
            font-size: 13px; color: #aaa; white-space: pre-wrap;
            border-left: 2px solid #FFD700;
        }

        .alerta { padding: 12px; border-radius: 8px; font-size: 14px; text-align: center; font-weight: bold; margin-bottom: 15px; }
        .alerta-sucesso { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid #2ed573; }
        .alerta-erro { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid #ff4757; }
        .sem-dados { color: #666; font-size: 14px; text-align: center; padding: 20px 0; }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15); box-sizing: border-box; margin-bottom: 30px;">
    
    <img src="assets/logo.png" class="logo">
    <h1>Controle de Fichas</h1>
    <p class="subtitle">Acompanhe, remova ou replique treinos entre alunos</p>

    <?php if (!empty($mensagem)): ?>
        <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <div class="lista-controle">
        <?php if (empty($treinos_destinados)): ?>
            <div class="sem-dados">
                <i class="fa-solid fa-folder-open" style="font-size: 24px; margin-bottom: 8px; display: block;"></i>
                Nenhum treino publicado no sistema.
            </div>
        <?php else: ?>
            <?php foreach ($treinos_destinados as $treino): ?>
                <div class="card-controle">
                    <div class="card-header">
                        <div class="aluno-info">
                            <span class="aluno-nome"><i class="fa-solid fa-user" style="font-size: 11px; color: #666; margin-right: 4px;"></i> <?php echo htmlspecialchars($treino['aluno_nome']); ?></span>
                            <span class="treino-tag"><?php echo htmlspecialchars($treino['titulo']); ?></span>
                        </div>
                        
                        <div class="acoes-card">
                            <!-- Visualizar Exercícios -->
                            <button class="btn-acao" onclick="toggleDetalhes(<?php echo $treino['id']; ?>)" title="Ver Exercícios">
                                <i class="fa-solid fa-eye" id="icone-<?php echo $treino['id']; ?>"></i>
                            </button>
                            
                            <!-- NOVO: Duplicar / Replicar Ficha -->
                            <a href="montar_treino.php?clonar=<?php echo $treino['id']; ?>" class="btn-acao btn-clonar" title="Duplicar para outro Aluno">
                                <i class="fa-solid fa-copy"></i>
                            </a>
                            
                            <!-- Excluir Ficha -->
                            <a href="controle_treinos.php?excluir=<?php echo $treino['id']; ?>" class="btn-acao btn-excluir" title="Excluir Ficha" onclick="return confirm('Excluir esta ficha permanentemente?')">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="detalhes-exercicios" id="detalhes-<?php echo $treino['id']; ?>"><?php echo htmlspecialchars($treino['descricao']); ?></div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 25px 0 20px 0;">
    <div style="display: flex; justify-content: space-between; width: 100%; box-sizing: border-box;">
        <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 13px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">
            <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
        </a>
        <a href="montar_treino.php" style="color: #FFD700; text-decoration: none; font-size: 13px; font-weight: bold;">
            <i class="fa-solid fa-plus"></i> Nova Ficha
        </a>
    </div>
</div>

<script>
function toggleDetalhes(id) {
    const painel = document.getElementById(`detalhes-${id}`);
    const icone = document.getElementById(`icone-${id}`);
    
    if (painel.style.display === "block") {
        painel.style.display = "none";
        icone.className = "fa-solid fa-eye";
    } else {
        painel.style.display = "block";
        icone.className = "fa-solid fa-eye-slash";
    }
}
</script>

</body>
</html>