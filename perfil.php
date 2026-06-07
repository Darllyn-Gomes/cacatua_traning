<?php
session_start();
require_once "conexao.php";
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->execute([$_SESSION['usuario_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .container { max-width: 500px; margin: auto; padding: 20px; }
        .tab-menu { display: flex; gap: 5px; margin-bottom: 25px; background: #222; padding: 5px; border-radius: 12px; }
        .tab-btn { flex: 1; padding: 12px; border: none; background: none; color: #aaa; cursor: pointer; border-radius: 8px; font-weight: bold; }
        .tab-btn.active { background: #FFD700; color: #000; }
        .section-tab { display: none; }
        .section-tab.active { display: block; }
        .input-group { margin-bottom: 18px; text-align: left; }
        label { display: block; color: #fff; font-size: 14px; margin-bottom: 8px; }
        input, select { width: 100%; padding: 14px; background: #1a1a1a; border: 1px solid #333; border-radius: 10px; color: #fff; }
        
        .feedback-box { padding: 15px; background: #1a2e1a; border: 1px solid #28a745; border-radius: 8px; color: #28a745; display: none; margin-top: 15px; text-align: center; font-weight: bold; }
        .financeiro-box { border: 1px solid #333; padding: 20px; border-radius: 15px; background: #222; }
        .link-voltar { color: #888; text-decoration: none; transition: 0.3s; display: inline-block; margin-top: 20px; }
        .link-voltar:hover { color: #FFD700; }
        .btn-amarelo { width:100%; padding:15px; background:#FFD700; border:none; border-radius:10px; font-weight:bold; color:#000; cursor:pointer; }
        
        /* Feedback Toast */
        #toast-sucesso { position:fixed; top:20px; right:20px; background:#1a2e1a; color:#28a745; padding:15px 25px; border-radius:8px; border:1px solid #28a745; display:none; z-index:9999; font-weight:bold; }
    </style>
</head>
<body>

<!-- Notificação de Sucesso -->
<div id="toast-sucesso">Dados salvos com sucesso!</div>

<div class="container">
    <h1 style="text-align:center; color:#FFD700; margin-bottom:25px;">Perfil do Usuário</h1>

    <div class="tab-menu">
        <button class="tab-btn active" onclick="openTab(event, 'pessoais')">Pessoal</button>
        <button class="tab-btn" onclick="openTab(event, 'saude')">Saúde</button>
        <button class="tab-btn" onclick="openTab(event, 'financeiro')">Financeiro</button>
    </div>

    <form action="atualizar_perfil.php" method="POST">
        <!-- Pessoais -->
        <div id="pessoais" class="section-tab active">
            <div class="input-group"><label>Nome Completo</label><input type="text" name="nome" value="<?= htmlspecialchars($user['nome'] ?? '') ?>"></div>
            <div class="input-group"><label>Nascimento</label><input type="date" name="data_nascimento" value="<?= $user['data_nascimento'] ?? '' ?>"></div>
            <div class="input-group"><label>Gênero</label>
                <select name="genero"><option value="m" <?= ($user['genero']??'')=='m'?'selected':'' ?>>Masculino</option><option value="f" <?= ($user['genero']??'')=='f'?'selected':'' ?>>Feminino</option></select>
            </div>
        </div>

        <!-- Saúde -->
        <div id="saude" class="section-tab">
            <div class="input-group"><label>Data Exame</label><input type="date" name="data_exame" value="<?= $user['data_exame'] ?? '' ?>"></div>
            <div class="input-group"><label>Status Aptidão</label>
                <select name="status_aptidao">
                    <option value="pendente" <?= ($user['status_aptidao']??'')=='pendente'?'selected':'' ?>>Pendente</option>
                    <option value="apto" <?= ($user['status_aptidao']??'')=='apto'?'selected':'' ?>>Apto</option>
                    <option value="apto_restricoes" <?= ($user['status_aptidao']??'')=='apto_restricoes'?'selected':'' ?>>Apto com Restrições</option>
                </select>
            </div>
            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                <div class="input-group"><label>Peso (kg)</label><input type="number" step="0.1" name="peso" id="peso" value="<?= $user['peso'] ?? '' ?>"></div>
                <div class="input-group"><label>Altura (cm)</label><input type="number" name="altura" id="altura" value="<?= $user['altura'] ?? '' ?>"></div>
            </div>
            <div style="background:#000; padding:15px; border-radius:10px; border:1px solid #FFD700;">
                <label style="color:#FFD700;">IMC Atual</label>
                <div id="imc-resultado" style="font-size:20px; font-weight:bold;"><?= number_format($user['imc'] ?? 0, 1) ?></div>
            </div>
        </div>

        <!-- Financeiro -->
        <div id="financeiro" class="section-tab">
            <div class="financeiro-box">
                <div class="input-group"><label>Plano atual</label><input type="text" value="Premium" disabled style="background:#111; color:#ccc;"></div>
                <hr style="border:0; border-top:1px solid #444; margin:20px 0;">
                <div class="input-group"><label>Deseja trocar de plano?</label>
                    <select id="novo_plano"><option value="">Não desejo alterar</option><option value="Basico">Básico</option><option value="Premium">Premium</option><option value="Elite">Elite</option></select>
                </div>
                <div class="input-group"><label>Deseja trocar pagamento?</label>
                    <select id="nova_forma_pagamento"><option value="">Não desejo alterar</option><option value="cartao">Cartão</option><option value="pix">PIX</option></select>
                </div>
                <button type="button" class="btn-amarelo" onclick="showFeedback()">Enviar Solicitações</button>
                <div id="feedback" class="feedback-box">Solicitações enviadas com sucesso!</div>
            </div>
        </div>

        <button type="submit" class="btn-amarelo" style="margin-top:20px;">Salvar Alterações</button>
    </form>
    <a href="dashboard.php" class="link-voltar">← Voltar ao Início</a>
</div>

<script>
    // Gerenciador do Toast de Sucesso
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('sucesso')) {
        const toast = document.getElementById('toast-sucesso');
        toast.style.display = 'block';
        setTimeout(() => { toast.style.display = 'none'; }, 3000);
        window.history.replaceState({}, document.title, window.location.pathname);
    }

    function openTab(evt, tabName) { document.querySelectorAll('.section-tab').forEach(t => t.classList.remove('active')); document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active')); document.getElementById(tabName).classList.add('active'); evt.currentTarget.classList.add('active'); }
    function calcularIMC() { let p = document.getElementById('peso').value; let a = document.getElementById('altura').value; if(p > 0 && a > 0) { let imc = p / ((a/100)**2); let status = imc < 18.5 ? "Abaixo do Peso" : (imc < 25 ? "Peso Ideal" : "Sobrepeso"); document.getElementById('imc-resultado').innerHTML = imc.toFixed(1) + " - " + status; } }
    document.getElementById('peso').addEventListener('input', calcularIMC); document.getElementById('altura').addEventListener('input', calcularIMC);
    function showFeedback() { const f = document.getElementById('feedback'); f.style.display = 'block'; setTimeout(() => f.style.display = 'none', 3000); }
</script>
</body>
</html>