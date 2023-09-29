<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];
    $senha = $_POST['senha'];

    require_once(__DIR__ . '/../../includes/db.php');

    // Consulta SQL para verificar o usuário
    $query = "SELECT id, senha, tipo_usuario, status_usuario FROM usuarios WHERE usuario = :usuario";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':usuario', $usuario);

    // Executar a consulta
    $statement->execute();

    // Verificar se o usuário foi encontrado
    $row = $statement->fetch(PDO::FETCH_ASSOC);

    if ($row && $row['status_usuario'] == 1) {
        // Obtenha o hash da senha do banco de dados
        $hashSenha = $row['senha'];

        // Verificar a senha usando password_verify
        if (password_verify($senha, $hashSenha)) {
            // Senha válida
            $idDoUsuario = $row['id'];
            $tipousuario = $row['tipo_usuario'];

            $_SESSION['usuario'] = $usuario;
            $_SESSION['senha'] = $senha;
            $_SESSION['id'] = $idDoUsuario; // Armazene o ID do usuário na sessão
            $_SESSION['tipo_usuario'] = $tipousuario;

            header('Location: painel.php');
            exit;
        } else {
            $_SESSION['mensagem'] = "Login ou senha incorretos. Tente novamente.";
            header('Location: ../../'); // Redirecionar de volta à página de login
            exit;
        }
    } elseif ($row && $row['status_usuario'] == 0) {
        $_SESSION['mensagem'] = "Seu usuário está desativado. Entre em contato com o administrador.";
        header('Location: ../../'); // Redirecionar de volta à página de login
        exit;
    } else {
        $_SESSION['mensagem'] = "Login ou senha incorretos. Tente novamente.";
        header('Location: ../../'); // Redirecionar de volta à página de login
        exit;
    }
}
?>