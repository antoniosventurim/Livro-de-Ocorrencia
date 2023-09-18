<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login');
    exit;
}

if (isset($_POST['cadretiradaveiculo'])) {
    $motoristaResponsavel = $_POST['usuarioResponsavel'];
    $nomeVeiculo = $_POST['nomeVeiculo'];
    $destinoVeiculo = $_POST['destino'];
    $dataRetirada = $_POST['dataRetirada'];

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirOcorrencia = "INSERT INTO retirada_veiculos (id_motorista, id_usuario, veiculo, destino, data_retirada, id_data_devolucao, data_registro) VALUES (:id_motorista, :id_usuario, :veiculo, :destino, :dataRetirada, NULL, NOW())";
    $statement = $pdo->prepare($queryInserirOcorrencia);
    $statement->bindParam(':id_motorista', $motoristaResponsavel); // Corrigido para id_motorista
    $statement->bindParam(':id_usuario', $idUsuarioLogado); // Use o ID do usuário logado
    $statement->bindParam(':veiculo', $nomeVeiculo);
    $statement->bindParam(':destino', $destinoVeiculo);
    $statement->bindParam(':dataRetirada', $dataRetirada);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel');
    exit;
}


// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
