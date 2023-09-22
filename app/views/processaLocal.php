<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login.ph');
    exit;
}

if (isset($_POST['cadlocal'])) {
    $local = $_POST['local'];
    $bloco = $_POST['bloco'];


    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirLocal = "INSERT INTO locais (nome_local, bloco, id_usuario, data_registro_local) VALUES (:nome_local, :bloco, :id_usuario, NOW())";
    $statement = $pdo->prepare($queryInserirLocal);
    $statement->bindParam(':nome_local', $local);
    $statement->bindParam(':bloco', $bloco);
    $statement->bindParam(':id_usuario', $idUsuarioLogado); // Use o ID do usuário logado
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel.ph');
    exit;
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
?>
