<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login');
    exit;
}

if (isset($_POST['devolucao'])) {
    $dataDevolucao = $_POST['dataDevolucao'];
    $idRetirada = $_POST['idRetiradaVeiculo'];

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    try {
        // Inicia uma transação
        $pdo->beginTransaction();

        // Insere a devolução na tabela devolucoes
        $queryInserirDevolucao = "INSERT INTO devolucoes (data_devolucao, id_retirada_veiculo, id_usuario_registrou) VALUES (:dataDevolucao, :idRetiradaVeiculo, :id_usuario_registrou)";
        $statement = $pdo->prepare($queryInserirDevolucao);
        $statement->bindParam(':dataDevolucao', $dataDevolucao);
        $statement->bindParam(':idRetiradaVeiculo', $idRetirada);
        $statement->bindParam(':id_usuario_registrou', $idUsuarioLogado); // Use o ID do usuário logado
        $statement->execute();

        // Recupera o ID da devolução inserida
        $idDevolucao = $pdo->lastInsertId();

        // Insere o ID da devolução na tabela retirada_veiculos na coluna id_data_devolucao
        $queryInserirIdDevolucao = "UPDATE retirada_veiculos SET id_data_devolucao = :idDevolucao WHERE id = :idRetiradaVeiculo";
        $statement = $pdo->prepare($queryInserirIdDevolucao);
        $statement->bindParam(':idDevolucao', $idDevolucao);
        $statement->bindParam(':idRetiradaVeiculo', $idRetirada);
        $statement->execute();

        // Confirma a transação
        $pdo->commit();

        // Redirecionar de volta para a página do painel após a inserção
        header('Location: painel');
        exit;
    } catch (PDOException $e) {
        // Em caso de erro, desfaz a transação
        $pdo->rollBack();
        echo 'Erro: ' . $e->getMessage();
    }
}




// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
