<?php
session_start();
require_once "conexao.php";

// TRAVA DE SEGURANÇA: Bloqueia acesso se não for professor ou adm
if (!isset($_SESSION['usuario_nivel']) || ($_SESSION['usuario_nivel'] !== 'professor' && $_SESSION['usuario_nivel'] !== 'adm')) {
    header("Location: dashboard.php");
    exit();
}

$feedback = "";
$banco_exercicios = [
    "peito" => ["titulo" => "Peito/Tríceps", "itens" => ["Supino Reto", "Supino Inclinado", "Pec Deck", "Tríceps Corda"]],
    "costas" => ["titulo" => "Costas/Bíceps", "itens" => ["Puxada Alta", "Remada Baixa", "Rosca Direta", "Rosca Concentrada"]],
    "pernas" => ["titulo" => "Pernas/Ombros", "itens" => ["Agachamento", "Leg Press", "Desenvolvimento", "Elevação Lateral"]]
];

// Lógica de Salvamento
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aluno_id'])) {
    $aluno_id = intval($_POST['aluno_id']);
    $professor_id = $_SESSION['usuario_id']; // ID de quem está montando o treino
    $exercicios = isset($_POST['exercicios']) ? implode("\n", $_POST['exercicios']) : '';

    if (!empty($aluno_id) && !empty($exercicios)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO treinos (aluno_id, professor_id, titulo, descricao) VALUES (:aluno_id, :professor_id, :titulo, :descricao)");
            $stmt->execute([
                ':aluno_id'    => $aluno_id,
                ':professor_id'=> $professor_id,
                ':titulo'      => "Treino Diário", 
                ':descricao'   => $exercicios
            ]);
            $feedback = '<div style="background: rgba(46, 213, 115, 0.1); color: #2ed573; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #2ed573; font-weight: bold;">Treino vinculado ao aluno com sucesso!</div>';
        } catch (PDOException $e) {
            $feedback = '<div style="background: rgba(255, 71, 87, 0.1); color: #ff4757; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #ff4757; font-weight: bold;">Erro ao salvar: ' . $e->getMessage() . '</div>';
        }
    } else {
        $feedback = '<div style="background: rgba(255, 165, 0, 0.1); color: #ffa500; padding: 15px; border-radius: 8px; margin-bottom: 20px; text-align: center; border: 1px solid #ffa500; font-weight: bold;">Selecione um aluno e pelo menos um exercício.</div>';
    }
}

$stmt = $pdo->query("SELECT id, nome FROM usuarios WHERE nivel = 'aluno' ORDER BY nome ASC");
$alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Montar Treino - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css"> 
    <style>
        select {
        background-color: #1a1a1a !important;
        color: #fff !important;
        border: 1px solid #444 !important;
        appearance: none; /* Remove o estilo padrão do SO */
        -webkit-appearance: none;
        -moz-appearance: none;
    }

    select option {
        background-color: #1a1a1a !important;
        color: #fff !important;
    }

    select:focus {
        background-color: #1a1a1a !important;
        outline: none;
        border-color: #FFD700 !important;
    }
        .input-app { width: 100%; height: 50px; background: transparent !important; border: 1px solid #444 !important; color: #fff !important; border-radius: 8px; padding: 0 15px; margin-bottom: 15px; display: block; box-sizing: border-box; }
        .input-app:hover { border-color: #FFD700 !important; cursor: pointer; }
        .btn-primary { width: 100%; padding: 15px; cursor: pointer; font-weight: bold; background: #FFD700; color: #000; border: none; border-radius: 8px; }
        .btn-secundario { 
        width: 100%; padding: 12px; cursor: pointer;
        background: transparent; color: #FFD700; border: 1px solid #FFD700; border-radius: 8px;
        margin-top: 10px;
        transition: all 0.3s ease;
    }

    .btn-secundario:hover {
        background: rgba(255, 215, 0, 0.05); /* Mais sutil: apenas 5% de opacidade */
        color: #FFD700;
        border-color: #FFD700;
    }

    /* Estilo para o link Voltar */
/* Estilo para o link Voltar */
/* Estilo para o link Voltar */
.btn-voltar {
    color: #666 !important; 
    text-decoration: none !important;
    transition: all 0.3s ease !important;
    display: inline-block;
    padding: 10px;
}

/* O hover amarelo solicitado */
.btn-voltar:hover {
    color: #FFD700 !important;
}
        .tabs { display: flex; border-bottom: 2px solid #333; margin: 20px 0; }
        .tab-btn { flex: 1; text-align: center; padding: 12px 5px; cursor: pointer; color: #fff; font-size: 13px; opacity: 0.6; }
        .tab-btn.active { opacity: 1; border-bottom: 2px solid #FFD700; color: #FFD700; font-weight: bold; }
        .grupo-exercicios { display: none; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 20px; }
        .grupo-exercicios.active { display: grid; }
        .item-exercicio { background: #1a1a1a; padding: 12px; border-radius: 6px; cursor: pointer; text-align: center; border: 1px solid #333; color: #fff; }
        .item-exercicio.selecionado { border-color: #FFD700; background: #262100; }
    </style>
</head>
<body>

<div class="container">
    <h1>Montar Treino</h1>
    <?php echo $feedback; ?>
    
    <form method="POST" class="form-treino">
        <select name="aluno_id" class="input-app" required>
            <option value="">Selecione o Aluno</option>
            <?php foreach ($alunos as $a): ?><option value="<?=$a['id']?>"><?=htmlspecialchars($a['nome'])?></option><?php endforeach; ?>
        </select>

        <div class="tabs">
            <?php foreach ($banco_exercicios as $k => $d): ?>
                <div class="tab-btn" onclick="mudarTab(this, '<?=$k?>')"><?=$d['titulo']?></div>
            <?php endforeach; ?>
        </div>

        <?php foreach ($banco_exercicios as $k => $d): ?>
            <div class="grupo-exercicios" id="tab-<?=$k?>">
                <?php foreach ($d['itens'] as $ex): ?>
                    <div class="item-exercicio" onclick="this.classList.toggle('selecionado'); this.querySelector('input').checked = this.classList.contains('selecionado');">
                        <input type="checkbox" name="exercicios[]" value="<?=$ex?>" style="display:none;">
                        <?=$ex?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

        <div style="border-top: 1px solid #333; padding-top: 20px;">
            <input type="text" id="novo_ex" class="input-app" placeholder="Nome do exercício extra">
            <select id="grupo_extra" class="input-app" style="margin: 10px 0;">
                <?php foreach($banco_exercicios as $k => $d): ?><option value="<?=$k?>"><?=$d['titulo']?></option><?php endforeach; ?>
            </select>
            <button type="button" class="btn-secundario" onclick="adicionarExtra()">+ Adicionar exercício à lista</button>
        </div>

        <button type="submit" class="btn-primary" style="margin-top: 25px;">SALVAR FICHA DE TREINO</button>
        
        <div style="text-align: center; margin-top: 30px;">
    <a href="dashboard.php" class="btn-voltar">&larr; Voltar ao Início</a>
</div>
    </form>
</div>

<script>
function mudarTab(el, id) {
    document.querySelectorAll('.tab-btn').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.grupo-exercicios').forEach(g => g.classList.remove('active'));
    el.classList.add('active');
    document.getElementById('tab-'+id).classList.add('active');
}
function adicionarExtra() {
    const nome = document.getElementById('novo_ex').value;
    const grupo = document.getElementById('grupo_extra').value;
    if(!nome) return;
    const div = document.createElement('div');
    div.className = 'item-exercicio selecionado';
    div.onclick = function() { this.classList.toggle('selecionado'); this.querySelector('input').checked = this.classList.contains('selecionado'); };
    div.innerHTML = `<input type="checkbox" name="exercicios[]" value="${nome}" checked style="display:none;">${nome}`;
    document.getElementById('tab-'+grupo).appendChild(div);
    document.getElementById('novo_ex').value = '';
}
document.querySelector('.tab-btn').click();
</script>
</body>
</html>