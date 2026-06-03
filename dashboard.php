<?php
session_start();
require_once "conexao.php";

// TRAVA DE SEGURANÇA: Só entra na dashboard se estiver logado no sistema
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$nome_usuario = $_SESSION['usuario_nome'];
$nivel_usuario = $_SESSION['usuario_nivel']; // Pode ser 'aluno', 'professor' ou 'adm'

// CONFIGURAÇÃO DINÂMICA DE CORES BASEADA NO NÍVEL
if ($nivel_usuario === 'adm') {
    $cor_primaria = "#ff4757";
    $bg_badge     = "rgba(255, 71, 87, 0.1)";
    $border_badge = "rgba(255, 71, 87, 0.25)";
    $icone_avatar = "fa-solid fa-user-gear";
} else {
    $cor_primaria = "#FFD700";
    $bg_badge     = "rgba(255, 215, 0, 0.08)";
    $border_badge = "rgba(255, 215, 0, 0.2)";
    $icone_avatar = "fa-solid fa-user-tie";
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cacatua Training</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"/>
    <style>
        /* Estilização do Novo Cabeçalho Moderno */
        .header-usuario {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.06);
            padding: 16px;
            border-radius: 14px;
            margin-top: 15px;
            text-align: left;
        }
        
        .boas-vindas-txt {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .boas-vindas-txt span {
            font-size: 13px;
            color: #888;
        }

        .boas-vindas-txt strong {
            font-size: 20px;
            color: #fff;
            font-weight: 700;
            letter-spacing: -0.3px;
        }

        .badge-nivel {
            font-size: 10px;
            background: <?php echo $bg_badge; ?>;
            color: <?php echo $cor_primaria; ?>;
            border: 1px solid <?php echo $border_badge; ?>;
            padding: 4px 10px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            margin-top: 4px;
            align-self: flex-start;
        }

        .avatar-wrapper {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            width: 46px;
            height: 46px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: <?php echo $cor_primaria; ?>;
            font-size: 16px;
        }

        .menu-dashboard { 
            display: flex; 
            flex-direction: column; 
            gap: 12px; 
            margin-top: 20px; 
            width: 100%; 
        }
        
        .item-menu-app {
            display: flex; 
            align-items: center; 
            gap: 14px; 
            background: rgba(255, 255, 255, 0.02); 
            border: 1px solid rgba(255, 255, 255, 0.08); 
            padding: 16px; 
            border-radius: 12px; 
            color: #fff; 
            text-decoration: none; 
            transition: all 0.2s ease;
        }
        .item-menu-app:hover {
            border-color: #FFD700;
            background: rgba(255, 215, 0, 0.04);
        }
        
        <?php if ($nivel_usuario === 'adm'): ?>
        .item-menu-app:hover {
            border-color: #ff4757;
            background: rgba(255, 71, 87, 0.02);
        }
        <?php endif; ?>
        
        .icon-wrapper {
            background: <?php echo ($nivel_usuario === 'adm') ? 'rgba(255, 71, 87, 0.1)' : 'rgba(255, 215, 0, 0.1)'; ?>; 
            width: 42px; 
            height: 42px; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center;
            color: <?php echo $cor_primaria; ?>; 
            font-size: 18px;
        }
        
        .texto-menu { 
            flex: 1; 
            text-align: left; 
        }
        .texto-menu strong { 
            display: block; 
            font-size: 14px; 
            color: #fff; 
        }
        .texto-menu span { 
            font-size: 12px; 
            color: #777; 
            transition: color 0.2s;
        }
        .item-menu-app:hover .texto-menu span { 
            color: #aaa; 
        }
        
        .seta-menu { 
            color: #333; 
            font-size: 12px; 
            transition: all 0.2s; 
        }
        .item-menu-app:hover .seta-menu { 
            color: <?php echo $cor_primaria; ?>; 
            transform: translateX(3px); 
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; border: 1px solid rgba(255, 255, 255, 0.15); box-sizing: border-box; padding-bottom: 30px;">
    
    <img src="assets/logo.png" class="logo" style="margin-bottom: 10px;">
    
    <div class="header-usuario">
        <div class="boas-vindas-txt">
            <span>Olá, bom dia! 👋</span>
            <strong><?php echo htmlspecialchars($nome_usuario); ?></strong>
            <div class="badge-nivel">
                <i class="fa-solid fa-user-shield" style="font-size: 9px;"></i> <?php echo htmlspecialchars($nivel_usuario); ?>
            </div>
        </div>
        <div class="avatar-wrapper">
            <i class="<?php echo $icone_avatar; ?>"></i>
        </div>
    </div>

    <div class="menu-dashboard">
        
        <?php if ($nivel_usuario === 'professor' || $nivel_usuario === 'adm'): ?>
            <a href="montar_treino.php" class="item-menu-app">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-dumbbell"></i>
                </div>
                <div class="texto-menu">
                    <strong>Montar Treino</strong>
                    <span>Criar nova ficha de exercícios</span>
                </div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>

            <a href="controle_treinos.php" class="item-menu-app">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-list-check"></i>
                </div>
                <div class="texto-menu">
                    <strong>Controle de Fichas</strong>
                    <span>Visualizar e gerenciar treinos ativos</span>
                </div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>

        <?php else: ?>
            <a href="meus_treinos.php" class="item-menu-app">
                <div class="icon-wrapper">
                    <i class="fa-solid fa-calendar-day"></i>
                </div>
                <div class="texto-menu">
                    <strong>Meu Treino de Hoje</strong>
                    <span>Visualizar a minha ficha atual</span>
                </div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>
        <?php endif; ?>

    </div>

    <hr style="border: 0; border-top: 1px solid rgba(255,255,255,0.08); margin: 30px 0 20px 0;">

    <a href="logout.php" style="color: #ff4757; text-decoration: none; font-size: 13px; font-weight: bold; display: inline-block;" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
        <i class="fa-solid fa-right-from-bracket"></i> Sair do Aplicativo
    </a>
</div>

</body>
</html>