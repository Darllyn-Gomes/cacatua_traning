<?php
session_start();
require_once "conexao.php";

// Se o botão de voltar for clicado, ignora qualquer processamento desta página e vai direto
if (isset($_GET['forcar_volta'])) {
    header("Location: dashboard.php");
    exit;
}

// TRAVA DE SEGURANÇA: Apenas Administrador acessa
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_nivel'] !== 'adm') {
    header("Location: dashboard.php");
    exit;
}

$mensagem = "";
$tipo_alerta = "sucesso";

// PROCESSAMENTO DAS AÇÕES
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
                    if (in_array($novo_nivel, ['aluno', 'professor', 'adm'])) {
                        $stmt = $pdo->prepare("UPDATE usuarios SET nivel = :nivel WHERE id = :id");
                        $stmt->execute([':nivel' => $novo_nivel, ':id' => $id_usuario]);
                        $mensagem = "Nível atualizado com sucesso!";
                    }
                } elseif ($_POST['acao'] === 'excluir') {
                    $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
                    $stmt->execute([':id' => $id_usuario]);
                    $mensagem = "Usuário removido com sucesso.";
                }
            } catch (PDOException $e) {
                $mensagem = "Erro no processamento: " . $e->getMessage();
                $tipo_alerta = "erro";
            }
        }
    }
}

// BUSCA DOS USUÁRIOS
try {
    $stmt = $pdo->prepare("SELECT id, nome, email, nivel, sexo FROM usuarios WHERE id != :id_atual ORDER BY nome ASC");
    $stmt->execute([':id_atual' => $_SESSION['usuario_id']]);
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erro ao carregar usuários: " . $e->getMessage());
}
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
        .lista-usuarios {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 20px;
            width: 100%;
        }

        .card-usuario {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            padding: 15px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            text-align: left;
            box-sizing: border-box;
        }

        .card-topo {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .info-principal h3 {
            margin: 0;
            font-size: 16px;
            color: #fff;
        }

        .info-principal p {
            margin: 2px 0 0 0;
            font-size: 13px;
            color: #666;
        }

        .card-controles {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 5px;
            padding-top: 10px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
        }

        .select-nivel-mobile {
            background: #141414;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 6px 10px;
            border-radius: 6px;
            font-size: 13px;
            outline: none;
            cursor: pointer;
        }

        .select-nivel-mobile:focus {
            border-color: #ff4757;
        }

        .btn-deletar-mobile {
            background: transparent;
            border: none;
            color: #ff4757;
            cursor: pointer;
            font-size: 16px;
            padding: 5px 10px;
        }

        .alerta { 
            padding: 12px; 
            border-radius: 8px; 
            font-size: 14px; 
            margin-bottom: 15px; 
            text-align: center; 
            font-weight: bold;
        }
        .alerta-sucesso { background: rgba(46, 213, 115, 0.15); color: #2ed573; border: 1px solid #2ed573; }
        .alerta-erro { background: rgba(255, 71, 87, 0.15); color: #ff4757; border: 1px solid #ff4757; }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15); box-sizing: border-box;">
    
    <img src="assets/logo.png" class="logo">
    <h1>Usuários</h1>
    <p class="subtitle">Gerencie permissões de professores e alunos</p>

    <?php if (!empty($mensagem)): ?>
        <div class="alerta alerta-<?php echo $tipo_alerta; ?>">
            <?php echo $mensagem; ?>
        </div>
    <?php endif; ?>

    <div class="lista-usuarios">
        <?php if (count($usuarios) > 0): ?>
            <?php foreach ($usuarios as $usr): ?>
                <div class="card-usuario">
                    
                    <div class="card-topo">
                        <div class="info-principal">
                            <h3>
                                <i class="fa-solid <?php echo $usr['sexo'] === 'f' ? 'fa-venus' : 'fa-mars'; ?>" style="opacity: 0.4; font-size: 13px; margin-right: 4px;"></i>
                                <?php echo htmlspecialchars($usr['nome']); ?>
                            </h3>
                            <p><?php echo htmlspecialchars($usr['email']); ?></p>
                        </div>
                    </div>

                    <div class="card-controles">
                        <form method="POST" action="" style="margin: 0;">
                            <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                            <input type="hidden" name="acao" value="alterar_nivel">
                            <select name="nivel" class="select-nivel-mobile" onchange="this.form.submit()">
                                <option value="aluno" <?php echo $usr['nivel'] === 'aluno' ? 'selected' : ''; ?>>Aluno</option>
                                <option value="professor" <?php echo $usr['nivel'] === 'professor' ? 'selected' : ''; ?>>Professor</option>
                                <option value="adm" <?php echo $usr['nivel'] === 'adm' ? 'selected' : ''; ?>>Admin</option>
                            </select>
                        </form>

                        <form method="POST" action="" style="margin: 0;" onsubmit="return confirm('Excluir este usuário permanentemente?');">
                            <input type="hidden" name="id_usuario" value="<?php echo $usr['id']; ?>">
                            <input type="hidden" name="acao" value="excluir">
                            <button type="submit" class="btn-deletar-mobile">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>

                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p style="color: #666; font-size: 14px; text-align: center; margin: 20px 0;">Nenhum outro usuário cadastrado.</p>
        <?php endif; ?>
    </div>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 25px 0 20px 0;">

    <a href="dashboard.php" style="color: #666; text-decoration: none; font-size: 13px; display: inline-block;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#666'">
        <i class="fa-solid fa-arrow-left"></i> Voltar ao Painel
    </a>
</div>

</body>
</html>