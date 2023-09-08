<?php
session_start();

// Função para criar um hash de senha
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    require_once(__DIR__ . '/../../includes/db.php');

    // Consulta SQL para verificar o usuário
    $query = "SELECT id, senha, tipo_usuario FROM usuarios WHERE usuario = :usuario AND senha = :senha";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':usuario', $usuario);
    $statement->bindParam(':senha', $senha);

    // Executar a consulta
    $statement->execute();

    // Verificar se o usuário foi encontrado
    if ($statement->rowCount() > 0) {
        // Obtenha o ID do usuário da consulta SQL
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        $idDoUsuario = $row['id'];
        $tipousuario = $row['tipo_usuario'];

        $_SESSION['usuario'] = $usuario;
        $_SESSION['senha'] = $senha;
        $_SESSION['id'] = $idDoUsuario; // Armazene o ID do usuário na sessão
        $_SESSION['tipo_usuario'] = $tipousuario;

        header('Location: painel');
        exit;
    } else {
        $_SESSION['mensagem'] = "Login ou senha incorretos. Tente novamente.";
        header('Location: ../../'); // Redirecionar de volta à página de login
        exit;
    }
}
