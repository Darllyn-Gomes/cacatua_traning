// Função que você já tinha para mostrar/esconder a senha
function mostrarSenha(id) {
    let campo = document.getElementById(id);
    if (campo.type == "password") {
        campo.type = "text";
    } else {
        campo.type = "password";
    }
}

function voltarLogin() {
    window.location.href = "../index.html";
}

function fecharJanela() {
    window.close();
}

// --- LÓGICA DE LOGIN E CREDENCIAIS DE TESTE ---
document.addEventListener("DOMContentLoaded", () => {
    // Credenciais de teste
    const emailTeste = "professor@cacatua.com";
    const senhaTeste = "123";

    // Mapeia os elementos do HTML pelas IDs que criamos
    const campoEmail = document.getElementById("email");
    const campoSenha = document.getElementById("senha");
    const btnTeste = document.getElementById("btn-teste");
    const btnEntrar = document.getElementById("btn-entrar");

    // 1. Ação ao clicar em "Preencher credenciais de teste"
    if (btnTeste && campoEmail && campoSenha) {
        btnTeste.addEventListener("click", () => {
            campoEmail.value = emailTeste;
            campoSenha.value = senhaTeste;
        });
    }

    // 2. Ação ao clicar no botão "Entrar como Professor"
    if (btnEntrar && campoEmail && campoSenha) {
        btnEntrar.addEventListener("click", () => {
            const emailDigitado = campoEmail.value.trim();
            const senhaDigitada = campoSenha.value;

            if (emailDigitado === emailTeste && senhaDigitada === senhaTeste) {
                alert("Login de teste realizado com sucesso!");
                // Redireciona para a página de logout (sua tela logada atual)
                window.location.href = "pages/logout.html";
            } else if (emailDigitado === "" || senhaDigitada === "") {
                alert("Por favor, preencha todos os campos ou clique em 'Preencher credenciais de teste'.");
            } else {
                alert("Credenciais incorretas. Clique na caixinha de teste para preencher automaticamente!");
            }
        });
    }
});