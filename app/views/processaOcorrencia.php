<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login');
    exit;
}

if (isset($_POST['cadastraOcorrencia'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'];
    $local = $_POST['local'];

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirOcorrencia = "INSERT INTO ocorrencias (titulo, descricao, local, id_responsavel, data_registro) VALUES (:titulo, :descricao, :local, :id_responsavel, NOW())";
    $statement = $pdo->prepare($queryInserirOcorrencia);
    $statement->bindParam(':titulo', $titulo);
    $statement->bindParam(':descricao', $descricao);
    $statement->bindParam(':local', $local);
    $statement->bindParam(':id_responsavel', $idUsuarioLogado); // Use o ID do usuário logado
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel');
    exit;
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
?>
