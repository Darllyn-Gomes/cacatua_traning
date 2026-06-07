<?php
session_start();
require_once "conexao.php";
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'aluno') { 
    header("Location: dashboard.php"); 
    exit(); 
}

$stmt = $pdo->prepare("SELECT * FROM treinos WHERE aluno_id = :aluno_id ORDER BY id DESC");
$stmt->execute([':aluno_id' => $_SESSION['usuario_id']]);
$treinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Meus Treinos</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
</head>
<body style="margin:0; background:#000; display:flex; justify-content:center; align-items:center; min-height:100vh; font-family:sans-serif;">

<div style="max-width:400px; width:100%; background:#141414; padding:20px; border-radius:12px; border:1px solid #333;">
    <h1 style="text-align:center; color:#fff; font-size:22px;">Minha Rotina</h1>
    
    <?php foreach ($treinos as $treino): ?>
        <div style="margin-bottom:25px;">
            <h3 style="color:#FFD700; border-bottom:1px solid #222; padding-bottom:10px;"><?php echo htmlspecialchars($treino['titulo']); ?></h3>
            <?php 
            $exercicios = explode("\n", $treino['descricao']); 
            foreach ($exercicios as $ex): 
                $ex = trim($ex);
                if (empty($ex)) continue;
                
                // Tenta separar pelo pipe
                $dados = explode("|", $ex); 
                $nome = $dados[0];
                $id_real = isset($dados[1]) ? (int)$dados[1] : 0;

                // SE NÃO ACHOU O ID NO PIPE, BUSCA NO BANCO PELO NOME
                if ($id_real == 0) {
                    $stmt_id = $pdo->prepare("SELECT id FROM exercicios WHERE nome = ?");
                    $stmt_id->execute([$nome]);
                    $res = $stmt_id->fetch(PDO::FETCH_ASSOC);
                    $id_real = $res ? (int)$res['id'] : 0;
                }
            ?>
                <div style="display:flex; align-items:center; justify-content:space-between; background:#1a1a1a; padding:12px; border-radius:8px; margin-bottom:8px; border:1px solid #333;">
                    <span style="color:#fff; font-weight:bold; font-size:14px;"><?php echo htmlspecialchars($nome); ?></span>
                    <button onclick="pedirSubstituicao(this, <?php echo $id_real; ?>)" 
                            style="background:transparent; border:1px solid #FFD700; color:#FFD700; padding:4px 8px; border-radius:4px; cursor:pointer; font-size:10px;">
                        <i class="fa-solid fa-sync"></i> Ocupado?
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <a href="dashboard.php" id="btn-voltar" style="display:block; text-align:center; color:#666; margin-top:20px; font-size:13px; text-decoration:none; transition:0.3s;">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
    </a>
</div>

<script>
// Hover amarelo no botão voltar
const btnVoltar = document.getElementById('btn-voltar');
btnVoltar.onmouseover = () => btnVoltar.style.color = '#FFD700';
btnVoltar.onmouseout = () => btnVoltar.style.color = '#666';

function pedirSubstituicao(btn, idPrincipal) {
    if(idPrincipal == 0) { 
        alert('Erro: Exercício não localizado no sistema para substituição.'); 
        return; 
    }
    
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    fetch('buscar_substituto.php?id_principal=' + idPrincipal)
        .then(r => r.json())
        .then(data => {
            if (data.sucesso) {
                btn.parentElement.querySelector('span').innerText = data.nome;
                btn.innerHTML = 'Ok';
                btn.style.borderColor = '#2ed573'; 
                btn.style.color = '#2ed573';
            } else {
                btn.innerHTML = 'Indisponível';
                alert(data.mensagem);
            }
        });
}
</script>
</body>
</html>