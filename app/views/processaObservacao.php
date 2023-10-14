<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

// Recebe os dados apos submit no formulário
if (isset($_POST['cadastra_observacao'])) {
    $observacao = $_POST['observacao'];
    $idUsuarioLogado = $_SESSION['id'];  
    $idOcorrencia = $_POST['ocorrencia_id'];
    
    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirObservacao = "INSERT INTO observacoes (id_ocorrencia, id_usuario, observacao, data_registro) VALUES (:idOcorrencia, :id_responsavel, :observacao, NOW())";
    $statement = $pdo->prepare($queryInserirObservacao);
    $statement->bindParam(':idOcorrencia', $idOcorrencia);
    $statement->bindParam(':id_responsavel', $idUsuarioLogado);
    $statement->bindParam(':observacao', $observacao);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel.php');
    exit;
}
//tratamento de erros aqui.
