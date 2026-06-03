<?php
session_start();

// Proteção: Apenas professores podem acessar
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'professor') {
    header("Location: index.php");
    exit;
}

$mensagem = "";

// Banco de dados simulado de exercícios disponíveis por grupo muscular
$exercicios_disponiveis = [
    'Peitoral' => ['Supino Reto', 'Supino Inclinado (Halteres)', 'Crucifixo Máquina', 'Cross Over'],
    'Costas & Bíceps' => ['Puxada Alta', 'Remada Baixa', 'Bíceps Rosca Direta', 'Bíceps Martelo'],
    'Membros Inferiores' => ['Agachamento Livre', 'Leg Press 45°', 'Cadeira Extensora', 'Mesa Flexora'],
    'Ombros & Tríceps' => ['Desenvolvimento Dumbbell', 'Elevação Lateral', 'Tríceps Pulley', 'Tríceps Corda']
];

// Processa o salvamento do treino estruturado
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aluno'])) {
    $aluno = $_POST['aluno'];
    $exercicios_selecionados = $_POST['exercicios'] ?? [];
    $series = $_POST['series'] ?? [];
    $repeticoes = $_POST['repeticoes'] ?? [];
    
    $ficha_estruturada = [];

    // Organiza os dados coletados de cada exercício marcado
    foreach ($exercicios_selecionados as $nome_exercicio) {
        // Remove espaços ou caracteres que quebrem o índice
        $chave_input = str_replace(' ', '_', $nome_exercicio);
        
        $ficha_estruturada[] = [
            'exercicio' => $nome_exercicio,
            'series' => !empty($series[$chave_input]) ? $series[$chave_input] : '4',
            'repeticoes' => !empty($repeticoes[$chave_input]) ? $repeticoes[$chave_input] : '12'
        ];
    }

    if (!empty($ficha_estruturada)) {
        // Salva a estrutura na sessão para o painel do aluno ler de forma organizada
        $_SESSION['treino_estruturado_' . $aluno] = $ficha_estruturada;
        $mensagem = "Sucesso: Nova rotina de treino gerada e vinculada!";
    } else {
        $mensagem = "Erro: Selecione ao menos um exercício para montar a ficha.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professor - Montar Ficha Prática</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .select-aluno {
            width: 100%;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 12px;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            margin-bottom: 20px;
            outline: none;
        }
        .grupo-muscular-box {
            text-align: left;
            margin-bottom: 18px;
            background: rgba(255, 255, 255, 0.02);
            padding: 12px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .grupo-titulo {
            font-size: 14px;
            color: #FFD700;
            margin-bottom: 10px;
            font-weight: bold;
            border-bottom: 1px solid rgba(255, 215, 0, 0.2);
            padding-bottom: 4px;
        }
        .linha-exercicio {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }
        .checkbox-container {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .inputs-treino {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        .inputs-treino input {
            width: 45px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 4px;
            color: white;
            text-align: center;
            padding: 4px 0;
            font-size: 13px;
        }
        .inputs-treino span {
            font-size: 11px;
            color: #777;
        }
        .container-rolavel {
            max-height: 380px;
            overflow-y: auto;
            margin-bottom: 20px;
            padding-right: 5px;
        }
        /* Customizando a barra de rolagem para combinar com o tema */
        .container-rolavel::-webkit-scrollbar { width: 6px; }
        .container-rolavel::-webkit-scrollbar-thumb { background: rgba(255, 215, 0, 0.3); border-radius: 4px; }
    </style>
</head>
<body>

<div class="container" style="border: 2px solid #FFD700; max-width: 460px;">
    <img src="assets/logo.png" class="logo">

    <h1>Prescrever Rotina</h1>
    <p class="subtitle">Selecione os exercícios com cliques rápidos</p>

    <?php if (!empty($mensagem)): ?>
        <div style="background: <?php echo strpos($mensagem, 'Erro') === false ? 'rgba(46, 213, 115, 0.15)' : 'rgba(255, 71, 87, 0.15)'; ?>; 
                    color: <?php echo strpos($mensagem, 'Erro') === false ? '#2ed573' : '#ff4757'; ?>; 
                    border: 1px solid <?php echo strpos($mensagem, 'Erro') === false ? '#2ed573' : '#ff4757'; ?>; 
                    padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; font-weight: bold;">
            <i class="fa-solid <?php echo strpos($mensagem, 'Erro') === false ? 'fa-circle-check' : 'fa-circle-xmark'; ?>"></i> 
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label style="display:block; text-align:left; color:#aaa; font-size:13px; margin-bottom:5px;">Aluno Alvo:</label>
        <select name="aluno" class="select-aluno">
            <option value="darllyn">Darllyn (Aluno)</option>
        </select>

        <div class="container-rolavel">
            <?php foreach ($exercicios_disponiveis as $grupo => $lista): ?>
                <div class="grupo-muscular-box">
                    <div class="grupo-titulo"><i class="fa-solid fa-tags"></i> <?php echo $grupo; ?></div>
                    
                    <?php foreach ($lista as $item): 
                        $chave = str_replace(' ', '_', $item);
                    ?>
                        <div class="linha-exercicio">
                            <label class="checkbox-container">
                                <input type="checkbox" name="exercicios[]" value="<?php echo $item; ?>">
                                <span><?php echo $item; ?></span>
                            </label>
                            
                            <div class="inputs-treino">
                                <input type="number" name="series[<?php echo $chave; ?>]" placeholder="Séries" value="4" title="Séries">
                                <span>x</span>
                                <input type="number" name="repeticoes[<?php echo $chave; ?>]" placeholder="Reps" value="12" title="Repetições">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" style="background: #FFD700; color: #111; font-weight: bold;">Finalizar e Entregar Ficha</button>
    </form>

    <a href="dashboard.php" style="margin-top: 15px; display: inline-block;">Voltar ao Painel</a>
</div>

</body>
</html>