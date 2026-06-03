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

// MECANISMO DE DUPLICAÇÃO / CLONAGEM DE FICHA
$treino_clonado_titulo = "Treino A";
$exercicios_clonados_nomes = [];
$observacoes_clonadas = [];

if (isset($_GET['clonar'])) {
    $clona_id = intval($_GET['clonar']);
    try {
        $stmtClona = $pdo->prepare("SELECT titulo, descricao FROM treinos WHERE id = :id");
        $stmtClona->execute([':id' => $clona_id]);
        $dadosClona = $stmtClona->fetch(PDO::FETCH_ASSOC);
        
        if ($dadosClona) {
            $treino_clonado_titulo = $dadosClona['titulo'];
            // Quebra as linhas para recuperar exercícios e observações
            $linhas = explode("\n", $dadosClona['descricao']);
            foreach ($linhas as $linha) {
                $linha = trim($linha);
                if (strpos($linha, "- ") === 0) {
                    // Extrai o nome do exercício e o que está entre parênteses
                    preg_match('/- (.*?) \((.*?)\)/', $linha, $matches);
                    if (count($matches) >= 3) {
                        $nome_ex = trim($matches[1]);
                        $obs_ex = trim($matches[2]);
                        $exercicios_clonados_nomes[] = $nome_ex;
                        $observacoes_clonadas[$nome_ex] = $obs_ex;
                    }
                }
            }
        }
    } catch (PDOException $e) {
        // Ignora falhas de leitura na clonagem
    }
}

// ENDPOINT AJAX INTERNO: Retorna o último treino do aluno em tempo real
if (isset($_GET['api_historico_aluno'])) {
    header('Content-Type: application/json');
    $id_aluno_busca = intval($_GET['api_historico_aluno']);
    try {
        $stmtHist = $pdo->prepare("SELECT titulo, descricao FROM treinos WHERE aluno_id = :aluno_id ORDER BY id DESC LIMIT 1");
        $stmtHist->execute([':aluno_id' => $id_aluno_busca]);
        $uTreino = $stmtHist->fetch(PDO::FETCH_ASSOC);
        if ($uTreino) {
            echo json_encode(['sucesso' => true, 'titulo' => $uTreino['titulo'], 'descricao' => $uTreino['descricao']]);
        } else {
            echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum histórico encontrado para este aluno.']);
        }
    } catch (Exception $e) {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Erro ao processar histórico.']);
    }
    exit;
}

// PROCESSAMENTO DO FORMULÁRIO (SALVAR TREINO)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $aluno_id = intval($_POST['aluno_id']);
    $titulo = trim($_POST['titulo']);
    $exercicios_selecionados = isset($_POST['exercicios']) ? $_POST['exercicios'] : [];
    $observacoes_inputs = isset($_POST['obs_exercicio']) ? $_POST['obs_exercicio'] : [];
    
    $descricao = "";
    if (!empty($exercicios_selecionados)) {
        foreach ($exercicios_selecionados as $ex) {
            // Se o instrutor preencheu a carga/obs, usa ela. Senão, assume o padrão 3x12
            $obs_definida = (!empty($observacoes_inputs[$ex])) ? trim($observacoes_inputs[$ex]) : "3x12";
            $descricao .= "- " . $ex . " (" . $obs_definida . ")\n"; 
        }
    }

    if (empty($aluno_id) || empty($titulo) || empty($descricao)) {
        $mensagem = "Por favor, selecione o aluno, defina o nome do treino e marque pelo menos um exercício.";
        $tipo_alerta = "erro";
    } else {
        try {
            $stmt = $pdo->prepare("INSERT INTO treinos (aluno_id, professor_id, titulo, descricao) VALUES (:aluno_id, :professor_id, :titulo, :descricao)");
            $stmt->execute([
                ':aluno_id' => $aluno_id,
                ':professor_id' => $_SESSION['usuario_id'],
                ':titulo' => $titulo,
                ':descricao' => $descricao
            ]);
            
            $mensagem = "Treino salvo com sucesso! Redirecionando...";
            $tipo_alerta = "sucesso";
            header("Refresh: 1.5; url=controle_treinos.php");
        } catch (PDOException $e) {
            $mensagem = "Erro ao salvar: " . $e->getMessage();
            $tipo_alerta = "erro";
        }
    }
}

// BUSCA OS ALUNOS PARA O SELECT
try {
    $stmt = $pdo->query("SELECT id, nome FROM usuarios WHERE nivel = 'aluno' ORDER BY nome ASC");
    $alunos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar alunos: " . $e->getMessage());
}

// Banco estruturado de exercícios padrão
$banco_exercicios = [
    "peito" => [
        "titulo" => "Peito/Tríceps",
        "itens" => ["Supino Reto", "Supino Inclinado", "Pec Deck", "Tríceps Corda"]
    ],
    "costas" => [
        "titulo" => "Costas/Bíceps",
        "itens" => ["Puxada Alta", "Remada Baixa", "Rosca Direta", "Rosca Concentrada"]
    ],
    "pernas" => [
        "titulo" => "Pernas/Ombros",
        "itens" => ["Agachamento", "Leg Press", "Desenvolvimento", "Elevação Lateral"]
    ]
];
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Montar Treino - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .form-treino { display: flex; flex-direction: column; gap: 18px; margin-top: 20px; width: 100%; text-align: left; }
        .grupo-campo { display: flex; flex-direction: column; gap: 6px; }
        .grupo-campo label { font-size: 14px; color: #ccc; font-weight: bold; }
        
        .input-app {
            background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px; padding: 12px; color: #fff; font-size: 14px; outline: none; width: 100%; box-sizing: border-box;
        }
        .input-app:focus { border-color: #FFD700; }
        select.input-app option { background: #141414; color: #fff; }

        /* Painel de Histórico */
        .painel-historico {
            background: rgba(255, 215, 0, 0.03); border: 1px dashed rgba(255, 215, 0, 0.2);
            border-radius: 8px; padding: 12px; font-size: 13px; display: none; color: #bbb;
        }

        .sugestoes { display: flex; gap: 5px; margin-top: 5px; flex-wrap: wrap; }
        .tag-sugestao {
            background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 5px 10px; border-radius: 4px; color: #aaa; font-size: 12px; cursor: pointer; transition: all 0.2s;
        }
        .tag-sugestao:hover { border-color: #FFD700; color: #fff; }

        .categoria-exercicios { margin-top: 14px; }
        .titulo-categoria { font-size: 12px; color: #FFD700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; border-left: 2px solid #FFD700; padding-left: 6px; }
        .lista-checkboxes { display: flex; flex-direction: column; gap: 10px; }
        
        /* Container estruturado para suportar o input de carga embutido */
        .item-checkbox {
            display: flex; flex-direction: column; background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05); padding: 12px; border-radius: 8px; cursor: pointer;
            transition: all 0.2s ease;
        }
        .checkbox-row { display: flex; align-items: center; width: 100%; }
        .item-checkbox input[type="checkbox"] { margin-right: 12px; accent-color: #FFD700; width: 18px; height: 18px; cursor: pointer; }
        .item-checkbox span { color: #ddd; font-size: 14px; flex: 1; }

        /* Input de Cargas Dinâmico */
        .input-obs-exercicio {
            display: none; margin-top: 8px; background: rgba(0,0,0,0.3); border: 1px solid rgba(255,255,255,0.1);
            border-radius: 6px; padding: 6px 10px; color: #FFD700; font-size: 12px; outline: none; width: 100%; box-sizing: border-box;
        }
        .input-obs-exercicio::placeholder { color: #555; }

        .item-checkbox.selecionado { background: rgba(255, 215, 0, 0.04); border-color: rgba(255, 215, 0, 0.3); }
        .item-checkbox.selecionado span { color: #FFD700; font-weight: bold; }
        .item-checkbox.selecionado .input-obs-exercicio { display: block; }

        .add-exercicio-container {
            background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 8px; padding: 14px; margin-top: 20px; display: flex; flex-direction: column; gap: 12px;
        }
        .btn-add-lista {
            background: rgba(255, 215, 0, 0.1); border: 1px solid #FFD700; color: #FFD700;
            padding: 12px; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: bold; text-align: center; transition: all 0.2s;
        }
        .btn-add-lista:hover { background: #FFD700; color: #000; }

        .btn-salvar-treino {
            background: rgba(255, 215, 0, 0.1); color: #FFD700; border: 1px solid #FFD700;
            padding: 14px; border-radius: 8px; font-size: 15px; font-weight: bold; cursor: pointer;
            transition: all 0.3s; margin-top: 15px; width: 100%;
        }
        .btn-salvar-treino:hover { background: #FFD700; color: #000; box-shadow: 0 0 15px rgba(255, 215, 0, 0.3); }
        
        .alerta { padding: 12px; border-radius: 8px; font-size: 14px; text-align: center; font-weight: bold; }
        .alerta-sucesso { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid #2ed573; }
        .alerta-erro { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid #ff4757; }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15); box-sizing: border-box; margin-bottom: 30px;">
    
    <img src="assets/logo.png" class="logo">
    <h1>Montar Treino</h1>
    <p class="subtitle">Insira as cargas e monte a programação do seu aluno</p>

    <?php if (!empty($mensagem)): ?>
        <div class="alerta alerta-<?php echo $tipo_alerta; ?>"><?php echo $mensagem; ?></div>
    <?php endif; ?>

    <form method="POST" action="" class="form-treino">
        
        <!-- 1. SELEÇÃO DO ALUNO + PAINEL DE HISTÓRICO INTELIGENTE -->
        <div class="grupo-campo">
            <label for="aluno_id"><i class="fa-solid fa-user" style="color: #FFD700; margin-right: 5px;"></i> Aluno</label>
            <select name="aluno_id" id="aluno_id" class="input-app" onchange="buscarHistoricoAluno(this.value)" required>
                <option value="">-- Selecione o Aluno --</option>
                <?php foreach ($alunos as $aluno): ?>
                    <option value="<?php echo $aluno['id']; ?>"><?php echo htmlspecialchars($aluno['nome']); ?></option>
                <?php endforeach; ?>
            </select>
            
            <!-- Painel carregado dinamicamente via JS -->
            <div id="historico-box" class="painel-historico"></div>
        </div>

        <!-- 2. IDENTIFICAÇÃO DO TREINO -->
        <div class="grupo-campo">
            <label for="titulo"><i class="fa-solid fa-folder-open" style="color: #FFD700; margin-right: 5px;"></i> Identificar Treino</label>
            <input type="text" name="titulo" id="titulo" class="input-app" value="<?php echo htmlspecialchars($treino_clonado_titulo); ?>" required>
            <div class="sugestoes">
                <span class="tag-sugestao" onclick="definirTreino('Treino A')">Treino A</span>
                <span class="tag-sugestao" onclick="definirTreino('Treino B')">Treino B</span>
                <span class="tag-sugestao" onclick="definirTreino('Treino C')">Treino C</span>
                <span class="tag-sugestao" onclick="definirTreino('Geral')">Geral</span>
            </div>
        </div>

        <!-- 3. LISTA DE EXERCÍCIOS COM SUPORTE A CARGAS -->
        <div class="grupo-campo">
            <label><i class="fa-solid fa-list-check" style="color: #FFD700; margin-right: 5px;"></i> Selecionar e Ajustar Exercícios</label>
            
            <div id="container-exercicios">
                <?php foreach ($banco_exercicios as $chave => $dados): ?>
                    <div class="categoria-exercicios">
                        <div class="titulo-categoria"><?php echo $dados['titulo']; ?></div>
                        <div class="lista-checkboxes" id="grupo-<?php echo $chave; ?>">
                            <?php foreach ($dados['itens'] as $exercicio): 
                                $marcado = in_array($exercicio, $exercicios_clonados_nomes);
                                $val_obs = $marcado ? $observacoes_clonadas[$exercicio] : "3x12";
                            ?>
                                <div class="item-checkbox <?php echo $marcado ? 'selecionado' : ''; ?>">
                                    <div class="checkbox-row" onclick="alternarSelecaoItem(this)">
                                        <input type="checkbox" name="exercicios[]" value="<?php echo $exercicio; ?>" <?php echo $marcado ? 'checked' : ''; ?>>
                                        <span><?php echo $exercicio; ?></span>
                                    </div>
                                    <input type="text" name="obs_exercicio[<?php echo $exercicio; ?>]" class="input-obs-exercicio" placeholder="Ex: 3x12 - 20kg ou Concentrado" value="<?php echo htmlspecialchars($val_obs); ?>">
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- ADICIONAR EXERCÍCIO EXTRA VERTICALIZADO -->
            <div class="add-exercicio-container">
                <span style="font-size: 11px; color: #FFD700; font-weight: bold; text-transform: uppercase;">Criar Exercício Extra</span>
                <div class="grupo-campo">
                    <select id="novo-exercicio-grupo" class="input-app">
                        <option value="peito">Peito/Tríceps</option>
                        <option value="costas">Costas/Bíceps</option>
                        <option value="pernas">Pernas/Ombros</option>
                    </select>
                </div>
                <div class="grupo-campo">
                    <input type="text" id="novo-exercicio-nome" class="input-app" placeholder="Nome do exercício customizado">
                </div>
                <button type="button" class="btn-add-lista" onclick="adicionarExercicioCustomizado()">
                    <i class="fa-solid fa-plus"></i> Inserir na Lista
                </button>
            </div>
        </div>

        <button type="submit" class="btn-salvar-treino">
            <i class="fa-solid fa-circle-check"></i> Salvar e Publicar Ficha
        </button>

    </form>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 25px 0 20px 0;">
    <div style="display: flex; justify-content: space-between; width: 100%; box-sizing: border-box;">
        <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 13px;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">
            <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
        </a>
        <a href="controle_treinos.php" style="color: #FFD700; text-decoration: none; font-size: 13px; font-weight: bold;">
            <i class="fa-solid fa-list-check"></i> Ver Fichas Ativas
        </a>
    </div>
</div>

<script>
function definirTreino(nome) {
    document.getElementById('titulo').value = nome;
}

// Manipula a seleção e exibe o campo de cargas correspondente
function alternarSelecaoItem(elementoRow) {
    const container = elementoRow.closest('.item-checkbox');
    const checkbox = container.querySelector('input[type="checkbox"]');
    
    checkbox.checked = !checkbox.checked;
    if (checkbox.checked) {
        container.classList.add('selecionado');
    } else {
        container.classList.remove('selecionado');
    }
}

// Carrega de forma assíncrona a última ficha do aluno selecionado
function buscarHistoricoAluno(idAluno) {
    const box = document.getElementById('historico-box');
    if (!idAluno) {
        box.style.display = 'none';
        return;
    }
    
    box.style.display = 'block';
    box.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Consultando histórico anterior...';
    
    fetch(`montar_treino.php?api_historico_aluno=${idAluno}`)
        .then(res => res.json())
        .then(data => {
            if (data.sucesso) {
                box.innerHTML = `<strong style="color:#FFD700;"><i class="fa-solid fa-clock-rotate-left"></i> Última ficha (${data.titulo}):</strong><br><span style="font-size:12px; white-space:pre-wrap; display:block; margin-top:4px;">${data.descricao}</span>`;
            } else {
                box.innerHTML = `<i class="fa-solid fa-info-circle"></i> ${data.mensagem}`;
            }
        })
        .catch(() => {
            box.style.display = 'none';
        });
}

function adicionarExercicioCustomizado() {
    const inputNome = document.getElementById('novo-exercicio-nome');
    const selectGrupo = document.getElementById('novo-exercicio-grupo');
    const nomeExercicio = inputNome.value.trim();
    const grupoAlvo = selectGrupo.value;
    
    if (nomeExercicio === '') return;
    const listaDestino = document.getElementById(`grupo-${grupoAlvo}`);
    
    if (listaDestino) {
        const div = document.createElement('div');
        div.className = 'item-checkbox selecionado';
        div.innerHTML = `
            <div class="checkbox-row" onclick="alternarSelecaoItem(this)">
                <input type="checkbox" name="exercicios[]" value="${nomeExercicio}" checked>
                <span>${nomeExercicio}</span>
            </div>
            <input type="text" name="obs_exercicio[${nomeExercicio}]" class="input-obs-exercicio" placeholder="Ex: 3x12 - 20kg" value="3x12" style="display:block;">
        `;
        
        listaDestino.appendChild(div);
        inputNome.value = '';
        div.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}
</script>

</body>
</html>