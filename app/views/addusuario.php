<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login');
    exit;
}

if (isset($_POST['adduser'])) {
    $tipoUsuario = $_POST['tipo_usuario'];
    $nome = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirUsuario = "INSERT INTO usuarios (tipo_usuario, nome, usuario, senha, data_registro) VALUES (:tipo_usuario, :nome, :usuario, :senha, NOW())";
    $statement = $pdo->prepare($queryInserirUsuario);
    $statement->bindParam(':nome', $nome);
    $statement->bindParam(':usuario', $usuario);
    $statement->bindParam(':senha', $senha);
    $statement->bindParam(':tipo_usuario', $tipoUsuario);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel');
    exit;
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
