<?php
session_start();
// Importa o arquivo de conexão que configuramos com o banco correto
require_once "conexao.php";

// Proteção: Apenas administradores podem acessar esta página
if (!isset($_SESSION['usuario_nivel']) || $_SESSION['usuario_nivel'] !== 'adm') {
    header("Location: index.php");
    exit;
}

$notificacao = "";
$tipo_alerta = "sucesso";

// ==========================================================================
// 1. PROCESSANDO A ALTERAÇÃO REAL NO BANCO DE DADOS (MYSQL)
// ==========================================================================
if (isset($_GET['alterar_id']) && isset($_GET['para'])) {
    $id = (int)$_GET['alterar_id'];
    $novo_cargo = $_GET['para']; // 'adm', 'professor' ou 'aluno'

    // Validação de segurança para garantir que o nível injetado na URL é válido
    if (in_array($novo_cargo, ['adm', 'professor', 'aluno'])) {
        try {
            // Primeiro, buscamos os dados atuais do usuário para personalizar a mensagem
            $stmt_busca = $pdo->prepare("SELECT nome, sexo FROM usuarios WHERE id = :id");
            $stmt_busca->execute([':id' => $id]);
            $usuario_alvo = $stmt_busca->fetch(PDO::FETCH_ASSOC);

            if ($usuario_alvo) {
                // Executa a atualização do nível no banco de dados
                $stmt_update = $pdo->prepare("UPDATE usuarios SET nivel = :nivel WHERE id = :id");
                $stmt_update->execute([
                    ':nivel' => $novo_cargo,
                    ':id' => $id
                ]);

                // Ajusta o gênero dinamicamente para o texto de feedback
                $termo_cargo = strtoupper($novo_cargo);
                if ($usuario_alvo['sexo'] === 'f') {
                    if ($novo_cargo === 'aluno') $termo_cargo = 'ALUNA';
                    if ($novo_cargo === 'professor') $termo_cargo = 'PROFESSORA';
                    if ($novo_cargo === 'adm') $termo_cargo = 'ADMINISTRADORA';
                }

                $notificacao = "Sucesso: '{$usuario_alvo['nome']}' agora é oficialmente $termo_cargo!";
                $tipo_alerta = "sucesso";
            }
        } catch (PDOException $e) {
            $notificacao = "Erro ao atualizar no banco de dados: " . $e->getMessage();
            $tipo_alerta = "erro";
        }
    }
}

// ==========================================================================
// 2. BUSCANDO OS USUÁRIOS DIRETOS DO BANCO DE DADOS para renderizar a lista
// ==========================================================================
try {
    $stmt_lista = $pdo->query("SELECT id, nome, email, nivel, sexo FROM usuarios ORDER BY nome ASC");
    $usuarios_banco = $stmt_lista->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao listar usuários: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Acessos - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        .lista-usuarios { margin-top: 20px; text-align: left; }
        .user-row {
            background: rgba(255, 255, 255, 0.03);
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .user-info h4 { font-size: 15px; margin: 0 0 4px 0; color: white; }
        .user-info span { font-size: 12px; color: #aaa; }
        
        /* Crachás Dinâmicos */
        .badge-table {
            padding: 3px 6px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            margin-left: 5px;
            display: inline-block;
            vertical-align: middle;
        }
        .badge-adm { background: #ff4757; color: white; }
        .badge-professor { background: #FFD700; color: #111; }
        .badge-aluno { background: #00adb5; color: #111; }

        .actions-btn { display: flex; gap: 5px; }
        .actions-btn a {
            padding: 6px 10px;
            font-size: 11px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.2s ease;
        }
        .actions-btn a:hover { background: rgba(255, 255, 255, 0.2); }
        
        .alerta { padding: 12px; border-radius: 8px; font-size: 14px; margin-bottom: 15px; font-weight: bold; text-align: center; }
        .alerta-sucesso { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid #2ed573; }
        .alerta-erro { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid #ff4757; }
    </style>
</head>
<body>

<div class="container" style="border: 2px solid #ff4757; max-width: 480px;">
    <img src="assets/logo.png" class="logo">

    <h1>Controle de Acesso</h1>
    <p class="subtitle">Gerenciar permissões reais via Banco de Dados</p>

    <!-- FEEDBACK VISUAL EM TEMPO REAL -->
    <?php if (!empty($notificacao)): ?>
        <div class="alerta alerta-<?php echo $tipo_alerta; ?>">
            <i class="fa-solid <?php echo $tipo_alerta === 'sucesso' ? 'fa-circle-check' : 'fa-circle-xmark'; ?>"></i> 
            <?php echo $notificacao; ?>
        </div>
    <?php endif; ?>

    <div class="lista-usuarios">
        
        <?php if (empty($usuarios_banco)): ?>
            <p style="text-align: center; color: #aaa; font-size: 14px; margin-top: 20px;">
                Nenhum usuário cadastrado na tabela 'usuarios' ainda.
            </p>
        <?php else: ?>
            <?php 
            foreach ($usuarios_banco as $usuario): 
                $id_user = $usuario['id'];
                $nivel_atual = $usuario['nivel'];
                $sexo = $usuario['sexo'];

                // Tratamento de gênero nativo baseado na coluna do MySQL
                $texto_badge = strtoupper($nivel_atual);
                if ($sexo === 'f') {
                    if ($nivel_atual === 'aluno') $texto_badge = 'ALUNA';
                    if ($nivel_atual === 'professor') $texto_badge = 'PROFESSORA';
                    if ($nivel_atual === 'adm') $texto_badge = 'ADM';
                }
            ?>
                <div class="user-row">
                    <div class="user-info">
                        <h4>
                            <?php echo htmlspecialchars($usuario['nome']); ?> 
                            <span class="badge-table badge-<?php echo $nivel_atual; ?>">
                                <?php echo $texto_badge; ?>
                            </span>
                        </h4>
                        <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                    </div>
                    
                    <div class="actions-btn">
                        <!-- Botões Condicionais de Promoção/Rebaixamento -->
                        <?php if ($nivel_atual === 'aluno'): ?>
                            <a href="controle_acesso.php?alterar_id=<?php echo $id_user; ?>&para=professor" style="color: #FFD700; border-color: rgba(255, 215, 0, 0.3);">
                                <i class="fa-solid fa-graduation-cap"></i> + PROF
                            </a>
                            <a href="controle_acesso.php?alterar_id=<?php echo $id_user; ?>&para=adm" style="color: #ff4757; border-color: rgba(255, 71, 87, 0.3);">
                                <i class="fa-solid fa-user-shield"></i> + ADM
                            </a>

                        <?php elseif ($nivel_atual === 'professor'): ?>
                            <a href="controle_acesso.php?alterar_id=<?php echo $id_user; ?>&para=aluno" style="color: #00adb5; border-color: rgba(0, 173, 181, 0.3);">
                                <i class="fa-solid fa-user"></i> + ALUNO
                            </a>
                            <a href="controle_acesso.php?alterar_id=<?php echo $id_user; ?>&para=adm" style="color: #ff4757; border-color: rgba(255, 71, 87, 0.3);">
                                <i class="fa-solid fa-user-shield"></i> + ADM
                            </a>

                        <?php elseif ($nivel_atual === 'adm'): ?>
                            <a href="controle_acesso.php?alterar_id=<?php echo $id_user; ?>&para=aluno" style="color: #00adb5; border-color: rgba(0, 173, 181, 0.3);">
                                <i class="fa-solid fa-user"></i> + ALUNO
                            </a>
                            <a href="controle_acesso.php?alterar_id=<?php echo $id_user; ?>&para=professor" style="color: #FFD700; border-color: rgba(255, 215, 0, 0.3);">
                                <i class="fa-solid fa-graduation-cap"></i> + PROF
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
    </div>

    <a href="dashboard.php" style="margin-top: 20px; display: inline-block;">Voltar ao Painel</a>
</div>

</body>
</html>