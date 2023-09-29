<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

if (isset($_POST['alterasenha'])) {
    $idUsuario = $_POST['id_usuario'];
    $novaSenha = $_POST['nova_senha'];

    $novaSenhaCriptografada = password_hash($novaSenha, PASSWORD_DEFAULT);
    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    // Processa o formulário e insere a ocorrência no banco de dados
    $queryAtualizaSenha= "UPDATE usuarios SET senha = :nova_senha WHERE id = :id_usuario";
    $statement = $pdo->prepare($queryAtualizaSenha);
    $statement->bindParam(':id_usuario', $idUsuario);
    $statement->bindParam(':nova_senha', $novaSenhaCriptografada);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: usuarios_cadastrados.php');
    exit;
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
?>