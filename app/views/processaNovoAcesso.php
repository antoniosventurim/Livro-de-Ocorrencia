<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

if (isset($_POST['cadastrar_acesso'])) {
    $nome = $_POST['nome'];
    $destino = $_POST['local'];
    $documento = $_POST['documento'];
    $tipo = $_POST['tipo'];
    $idUsuarioLogado = $_SESSION['id'];

    
    $queryInserirObservacao = "INSERT INTO acessos (nome, destino, documento, tipo_pessoa, data_acesso, usuario_id) VALUES (:nome, :destino, :documento, :tipo, NOW(), :idUsuarioLogado)";
    $statement = $pdo->prepare($queryInserirObservacao);
    $statement->bindParam(':nome', $nome);
    $statement->bindParam(':destino', $destino);
    $statement->bindParam(':documento', $documento);
    $statement->bindParam(':tipo', $tipo);
    $statement->bindParam(':idUsuarioLogado', $idUsuarioLogado);
    $statement->execute();

    // Redirecionar de volta para a página do painel após a inserção
    header('Location: painel.php');
    exit;
}
 echo "Erro";
?>
