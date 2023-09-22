<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login');
    exit;
}

if (isset($_POST['cadveiculo'])) {
    $tipoVeiculo = $_POST['tipo_veiculo'];
    $nomeVeiculo = $_POST['nome'];
    $placaVeiculo = $_POST['placa'];

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirOcorrencia = "INSERT INTO veiculos (tipo_veiculo, nome, placa, id_usuario, data_registro) VALUES (:tipo_Veiculo, :nomeVeiculo, :placaVeiculo, :id_usuario, NOW())";
    $statement = $pdo->prepare($queryInserirOcorrencia);
    $statement->bindParam(':tipo_Veiculo', $tipoVeiculo);
    $statement->bindParam(':nomeVeiculo', $nomeVeiculo);
    $statement->bindParam(':placaVeiculo', $placaVeiculo);
    $statement->bindParam(':id_usuario', $idUsuarioLogado); // Use o ID do usuário logado
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel');
    exit;
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
?>
