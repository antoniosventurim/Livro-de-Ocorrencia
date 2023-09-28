<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}
if (isset($_POST['alterastatususer'])) {
    $idUsuario = $_POST['id_usuario'];
    $novoStatus = $_POST['novo_status'];
    // Atualizar o status do usuário no banco de dados
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário


    // Verifique se uma opção válida foi selecionada
    if ($novoStatus !== null && $novoStatus !== "") {
        // Atualize o status do usuário no banco de dados
        $queryUpdateStatus = "UPDATE usuarios SET status_usuario = :novo_status WHERE id = :id_usuario";
        $statementUpdateStatus = $pdo->prepare($queryUpdateStatus);
        $statementUpdateStatus->bindParam(':novo_status', $novoStatus);
        $statementUpdateStatus->bindParam(':id_usuario', $idUsuario);

        if ($statementUpdateStatus->execute()) {
            // Status atualizado com sucesso
            $_SESSION['mensagem'] = "Status do usuário atualizado com sucesso.";
        } else {
            // Erro ao atualizar o status
            $_SESSION['mensagem'] = "Erro ao atualizar o status do usuário.";
        }
    } else {
        // Opção inválida selecionada, exibir mensagem de erro
        $_SESSION['mensagem'] = "Selecione uma opção válida para o status do usuário.";
    }

    // Redirecionar de volta à página de origem
    header('Location: usuarios_cadastrados.php');
    exit;
}
