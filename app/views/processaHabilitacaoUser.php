<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: login');
    exit;
}
    if (isset($_POST['alterastatususer'])) {
        $idUsuario = $_POST['id_usuario'];
        $novoStatus = $_POST['novo_status'];
    // Atualizar o status do usuário no banco de dados


        $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

        $queryAtualizarStatus = "UPDATE usuarios SET status_usuario = :novoStatus WHERE id = :id";
        $statement = $pdo->prepare($queryAtualizarStatus);
        $statement->bindParam(':id', $idUsuario);
        $statement->bindParam(':novoStatus', $novoStatus, PDO::PARAM_INT);
        $statement->execute();

        // Redirecionar de volta para a página de onde veio
        header('Location: painel');
        exit;
    }
