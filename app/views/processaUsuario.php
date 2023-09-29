<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

if (isset($_POST['adduser'])) {
    $tipoUsuario = $_POST['tipo_usuario'];
    $nome = $_POST['nome'];
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];
    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Verifique se já existe um usuário com o mesmo nome de usuário
    $queryVerificarUsuarioExistente = "SELECT id FROM usuarios WHERE usuario = :usuario";
    $statementVerificarUsuarioExistente = $pdo->prepare($queryVerificarUsuarioExistente);
    $statementVerificarUsuarioExistente->bindParam(':usuario', $usuario);
    $statementVerificarUsuarioExistente->execute();

    if ($statementVerificarUsuarioExistente->rowCount() > 0) {
        // Já existe um usuário com esse nome de usuário, exiba uma mensagem de erro
        $_SESSION['mensagem'] = "Nome de usuário já está em uso. Escolha outro.";
        header('Location: painel.php'); // Redirecione para a página de registro
        exit;
    }

    $hashSenha = password_hash($senha, PASSWORD_DEFAULT);

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryInserirUsuario = "INSERT INTO usuarios (tipo_usuario, nome, usuario, senha, data_registro) VALUES (:tipo_usuario, :nome, :usuario, :senha, NOW())";
    $statement = $pdo->prepare($queryInserirUsuario);
    $statement->bindParam(':nome', $nome);
    $statement->bindParam(':usuario', $usuario);
    $statement->bindParam(':senha', $hashSenha);
    $statement->bindParam(':tipo_usuario', $tipoUsuario);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: usuarios_cadastrados.php');
    exit;
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
