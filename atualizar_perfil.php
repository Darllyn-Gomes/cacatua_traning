<?php
session_start();
require_once "conexao.php";

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Recebe e sanitiza os dados do formulário
        $nome = $_POST['nome'];
        $data_nascimento = $_POST['data_nascimento'];
        $genero = $_POST['genero']; // 'm' ou 'f' conforme o ENUM do seu banco
        $peso = (float)$_POST['peso'];
        $altura_cm = (int)$_POST['altura'];
        $data_exame = $_POST['data_exame'];
        $status_aptidao = $_POST['status_aptidao']; // Ex: 'apto', 'apto_restricoes', 'pendente'

        // Cálculo do IMC: Altura em cm convertida para metros
        $altura_m = $altura_cm / 100;
        $imc = ($altura_m > 0) ? ($peso / ($altura_m ** 2)) : 0;

        // O comando SQL atualizado para a sua tabela 'usuarios'
        // Observação: certifique-se de que a coluna status_aptidao foi renomeada 
        // ou mude abaixo para 'status_exame' se ainda não tiver renomeado.
        $sql = "UPDATE usuarios SET 
                nome = ?, 
                data_nascimento = ?, 
                genero = ?, 
                peso = ?, 
                altura = ?, 
                imc = ?, 
                data_exame = ?, 
                status_aptidao = ? 
                WHERE id = ?";

        $stmt = $pdo->prepare($sql);
        
        $stmt->execute([
            $nome, 
            $data_nascimento, 
            strtolower($genero), 
            $peso, 
            $altura_cm, 
            $imc, 
            $data_exame, 
            $status_aptidao, 
            $_SESSION['usuario_id']
        ]);

        // Redireciona de volta para o perfil com aviso de sucesso
        header("Location: perfil.php?sucesso=1");
        exit;

    } catch (PDOException $e) {
        // Em caso de erro, exibe o que aconteceu para você corrigir
        die("Erro ao salvar no banco de dados: " . $e->getMessage());
    }
} else {
    // Se tentarem acessar este arquivo diretamente sem enviar dados, volta ao perfil
    header("Location: perfil.php");
    exit;
}
?>