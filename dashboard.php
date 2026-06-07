<?php
session_start();

date_default_timezone_set('America/Sao_Paulo');
require_once "conexao.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$u = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se já fez check-in hoje
$stmt_check = $pdo->prepare("SELECT id FROM checkins 
                             WHERE aluno_id = ? 
                             AND DATE(data_checkin) = CURDATE()");
$stmt_check->execute([$u['id']]);
$ja_fez_checkin = ($stmt_check->rowCount() > 0);

$nome_do_banco = trim($u['nome'] ?? '');
$nome_usuario = !empty($nome_do_banco) ? $nome_do_banco : explode('@', $u['email'])[0];

$nivel_usuario = $_SESSION['usuario_nivel'] ?? $u['nivel'] ?? 'aluno';

$hora = (int)date('H');
if ($hora >= 5 && $hora < 12) { $saudacao = "Bom dia! ☀️"; }
elseif ($hora >= 12 && $hora < 18) { $saudacao = "Boa tarde! 🌤️"; }
else { $saudacao = "Boa noite! 🌙"; }

if ($nivel_usuario === 'adm') {
    $cor_primaria = "#ff4757";
    $bg_badge     = "rgba(255, 71, 87, 0.1)";
    $border_badge = "rgba(255, 71, 87, 0.25)";
} else {
    $cor_primaria = "#FFD700";
    $bg_badge     = "rgba(255, 215, 0, 0.08)";
    $border_badge = "rgba(255, 215, 0, 0.2)";
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
        .logo { width: 220px; display: block; margin: 0 auto 20px auto; }
        .header-usuario { display: flex; align-items: center; justify-content: space-between; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06); padding: 18px; border-radius: 14px; margin-top: 15px; cursor: pointer; transition: 0.2s; }
        .boas-vindas-txt { display: flex; flex-direction: column; gap: 2px; text-align: left; }
        .boas-vindas-txt span { font-size: 14px; color: #888; }
        .boas-vindas-txt strong { font-size: 24px; color: #fff; font-weight: 700; text-transform: capitalize; }
        .badge-nivel { font-size: 11px; background: <?php echo $bg_badge; ?>; color: <?php echo $cor_primaria; ?>; border: 1px solid <?php echo $border_badge; ?>; padding: 5px 12px; border-radius: 20px; font-weight: bold; text-transform: uppercase; display: inline-flex; align-items: center; gap: 5px; margin-top: 6px; align-self: flex-start; }
        .avatar-wrapper { background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); width: 52px; height: 52px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: <?php echo $cor_primaria; ?>; font-size: 18px; }
        .menu-dashboard { display: flex; flex-direction: column; gap: 14px; margin-top: 25px; width: 100%; }
        .item-menu-app { display: flex; align-items: center; gap: 16px; background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.08); padding: 18px; border-radius: 12px; color: #fff; text-decoration: none; transition: all 0.2s ease; }
        .icon-wrapper { background: <?php echo ($nivel_usuario === 'adm') ? 'rgba(255, 71, 87, 0.1)' : 'rgba(255, 215, 0, 0.1)'; ?>; width: 44px; height: 44px; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: <?php echo $cor_primaria; ?>; font-size: 20px; }
        .texto-menu { flex: 1; text-align: left; }
        .texto-menu strong { display: block; font-size: 16px; color: #fff; transition: 0.2s; }
        .texto-menu span { font-size: 13px; color: #777; }
        .seta-menu { color: #333; font-size: 14px; transition: all 0.2s; }
        .header-usuario:hover { border: 1px solid <?php echo $cor_primaria; ?>; background: rgba(255, 255, 255, 0.05); }
        .item-menu-app:hover { border-color: <?php echo $cor_primaria; ?>; background: rgba(255, 255, 255, 0.05); }
        .item-menu-app:hover strong { color: <?php echo $cor_primaria; ?>; }
        .item-menu-app:hover .seta-menu { color: <?php echo $cor_primaria; ?>; transform: translateX(5px); }
    </style>
</head>
<body>

<div class="container" style="max-width: 400px; margin: 0 auto; padding: 20px;">
    
    <?php if (isset($_GET['checkin'])): ?>
        <div style="background: rgba(46, 213, 115, 0.1); color: #2ed573; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-size: 14px;">
            <i class="fa-solid fa-circle-check"></i> <?php echo ($_GET['checkin'] == 'sucesso') ? "Presença registrada!" : "Você já fez check-in hoje!"; ?>
        </div>
    <?php endif; ?>

    <img src="assets/logo.png" class="logo">
    
    <div class="header-usuario" onclick="window.location.href='perfil.php'">
        <div class="boas-vindas-txt">
            <span><?php echo $saudacao; ?></span>
            <strong><?php echo htmlspecialchars($nome_usuario); ?></strong>
            <div class="badge-nivel">
                <i class="fa-solid fa-user-shield"></i> <?php echo htmlspecialchars($nivel_usuario); ?>
            </div>
        </div>

        <a href="editar_foto.php" class="avatar-wrapper" style="text-decoration: none;">
           <?php 
            $foto = $u['foto_perfil'] ?? '';
            $caminho = (!empty($foto)) ? ((strpos($foto, 'cacatua') !== false) ? "assets/" . $foto : "uploads/" . $foto) : "assets/cacatua" . (($u['id'] % 5) + 1) . ".png";
            echo '<img src="'.$caminho.'" style="width:52px; height:52px; border-radius:50%; object-fit:cover;">';
            ?>
        </a>
    </div>

    <div class="menu-dashboard">
        <?php if ($nivel_usuario === 'professor' || $nivel_usuario === 'adm'): ?>
            <a href="montar_treino.php" class="item-menu-app">
                <div class="icon-wrapper"><i class="fa-solid fa-dumbbell"></i></div>
                <div class="texto-menu"><strong>Montar Treino</strong><span>Criar nova ficha de exercícios</span></div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>
            <a href="controle_treinos.php" class="item-menu-app">
                <div class="icon-wrapper"><i class="fa-solid fa-list-check"></i></div>
                <div class="texto-menu"><strong>Controle de Fichas</strong><span>Visualizar e gerenciar treinos</span></div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>
            <a href="ver_presencas.php" class="item-menu-app">
                <div class="icon-wrapper" style="background: rgba(46, 213, 115, 0.1); color: #2ed573;"><i class="fa-solid fa-clipboard-check"></i></div>
                <div class="texto-menu"><strong>Ver Presenças</strong><span>Alunos que treinaram hoje</span></div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>
        <?php else: ?>
            <a href="meus_treinos.php" class="item-menu-app">
                <div class="icon-wrapper"><i class="fa-solid fa-calendar-day"></i></div>
                <div class="texto-menu"><strong>Meu Treino de Hoje</strong><span>Visualizar a minha ficha atual</span></div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>

            <a href="<?php echo $ja_fez_checkin ? '#' : 'registrar_checkin.php'; ?>" class="item-menu-app" style="border-color: <?php echo $ja_fez_checkin ? '#2ed573' : $cor_primaria; ?>;">
                <div class="icon-wrapper" style="background: <?php echo $ja_fez_checkin ? 'rgba(46, 213, 115, 0.1)' : 'rgba(255, 215, 0, 0.1)'; ?>; color: <?php echo $ja_fez_checkin ? '#2ed573' : $cor_primaria; ?>;">
                    <i class="fa-solid <?php echo $ja_fez_checkin ? 'fa-circle-check' : 'fa-check-circle'; ?>"></i>
                </div>
                <div class="texto-menu">
                    <strong><?php echo $ja_fez_checkin ? 'Check-in Realizado' : 'Fazer Check-in'; ?></strong>
                    <span><?php echo $ja_fez_checkin ? 'Presença confirmada hoje!' : 'Registrar presença no treino'; ?></span>
                </div>
                <?php if (!$ja_fez_checkin): ?><i class="fa-solid fa-chevron-right seta-menu"></i><?php endif; ?>
            </a>
        <?php endif; ?>

        <?php if ($nivel_usuario === 'adm'): ?>
            <a href="gerenciar_usuarios.php" class="item-menu-app">
                <div class="icon-wrapper"><i class="fa-solid fa-users-gear"></i></div>
                <div class="texto-menu"><strong>Gerenciar Acessos</strong><span>Promover alunos ou professores</span></div>
                <i class="fa-solid fa-chevron-right seta-menu"></i>
            </a>
        <?php endif; ?>
    </div>

    <div style="margin-top: 30px; text-align: center;">
        <a href="logout.php" style="color: #ff4757; text-decoration: none; font-size: 14px; font-weight: bold;">
            <i class="fa-solid fa-right-from-bracket"></i> Sair do Aplicativo
        </a>
    </div>
</div>
</body>
</html>