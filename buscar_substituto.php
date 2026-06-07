<?php
require_once "conexao.php";

// Recebe o ID
$id_principal = isset($_GET['id_principal']) ? intval($_GET['id_principal']) : 0;

if ($id_principal <= 0) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'ID inválido: recebemos ' . $_GET['id_principal']]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT e.nome FROM exercicios_substitutos s 
                           JOIN exercicios e ON s.exercicio_id_substituto = e.id 
                           WHERE s.exercicio_id_principal = ?");
    $stmt->execute([$id_principal]);
    $substituto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($substituto) {
        echo json_encode(['sucesso' => true, 'nome' => $substituto['nome']]);
    } else {
        echo json_encode(['sucesso' => false, 'mensagem' => 'Nenhum substituto cadastrado para o ID ' . $id_principal]);
    }
} catch (Exception $e) {
    echo json_encode(['sucesso' => false, 'mensagem' => 'Erro no banco: ' . $e->getMessage()]);
}
?>