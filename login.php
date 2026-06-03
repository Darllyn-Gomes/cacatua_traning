<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login Professor</title>

<link rel="stylesheet" href="../css/style.css">

<body>

<div class="container">

<img src="../assets/logo.png" class="logo">

<h1>Login Professor</h1>
<p>Acesso para instrutores</p>

<span class="status">
<i class="fa-solid fa-wifi"></i>
Conectado ao servidor
</span>


<div class="input-box">
<i class="fa-solid fa-envelope"></i>
<input type="email" placeholder="E-mail">
</div>


<div class="input-box">

<i class="fa-solid fa-lock"></i>

<input type="password" id="senha" placeholder="Senha">

<i class="fa-solid fa-eye eye"
onclick="mostrarSenha('senha')"></i>

</div>


<button>Entrar como Professor</button>

<a href="pages/esqueceu.html">
Esqueceu a senha?
</a>

<a href="pages/cadastro.html">
Criar conta
</a>

</div>

<script src="js/appscript.js"></script>
</body>
</html>