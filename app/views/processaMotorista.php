<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

// RECEBE OS DADOS APOS O SUBMIT NO FORM
if (isset($_POST['cadmotorista'])) {
    $motorista = $_POST['motorista'];
    $setor = $_POST['setor'];
    $cpf = $_POST['cpf'];
    $idUsuarioLogado = $_SESSION['id'];

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirOcorrencia = "INSERT INTO motoristas (nome, setor, cpf, id_usuario, data_registro) VALUES (:motorista, :setor, :cpf, :id_usuario, NOW())";
    $statement = $pdo->prepare($queryInserirOcorrencia);
    $statement->bindParam(':motorista', $motorista);
    $statement->bindParam(':setor', $setor);
    $statement->bindParam(':cpf', $cpf);
    $statement->bindParam(':id_usuario', $idUsuarioLogado);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel.php');
    exit;
}

//tratar erros aqui.
?>
