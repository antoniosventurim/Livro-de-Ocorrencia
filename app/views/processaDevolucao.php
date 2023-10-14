<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

if (isset($_POST['devolucao'])) {
    $dataDevolucao = $_POST['dataDevolucao'];
    $idRetirada = $_POST['idRetiradaVeiculo'];
    $statusDevolucao = $_POST['statusDevolucao'];
    $idUsuarioLogado = $_SESSION['id'];

    try {
        // Inicia uma transação
        $pdo->beginTransaction();

        // Insere a devolução na tabela devolucoes
        $queryInserirDevolucao = "INSERT INTO devolucoes (data_devolucao, id_retirada_veiculo, id_usuario_registrou) VALUES (:dataDevolucao, :idRetiradaVeiculo, :id_usuario_registrou)";
        $statement = $pdo->prepare($queryInserirDevolucao);
        $statement->bindParam(':dataDevolucao', $dataDevolucao);
        $statement->bindParam(':idRetiradaVeiculo', $idRetirada);
        $statement->bindParam(':id_usuario_registrou', $idUsuarioLogado);
        $statement->execute();

        // Recupera o ID da devolução inserida
        $idDevolucao = $pdo->lastInsertId();

        // Insere o ID da devolução na tabela retirada_veiculos na coluna id_data_devolucao
        $queryInserirIdDevolucao = "UPDATE retirada_veiculos SET statusVeiculo =:statusdevolucao, id_data_devolucao = :idDevolucao WHERE id = :idRetiradaVeiculo";
        $statement = $pdo->prepare($queryInserirIdDevolucao);
        $statement->bindParam(':idDevolucao', $idDevolucao);
        $statement->bindParam(':statusdevolucao', $statusDevolucao);
        $statement->bindParam(':idRetiradaVeiculo', $idRetirada);
        $statement->execute();

        // Confirma a transação
        $pdo->commit();

        // Redirecionar de volta para a página do painel após a inserção
        header('Location: painel.php');
        exit;
    } catch (PDOException $e) {
        // Em caso de erro, desfaz a transação
        $pdo->rollBack();
        echo 'Erro: ' . $e->getMessage();
    }
}
//tratamento de erros aqui.
