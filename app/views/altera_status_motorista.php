<?php
// Arquivo verificar_local.php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

if (isset($_POST['id_motorista']) && isset($_POST['novo_status'])) {
    $idMotorista = $_POST['id_motorista'];
    $novoStatus = $_POST['novo_status'];

    // Prepare e execute a consulta SQL para atualizar o status do motorista
    $queryAtualizarStatus = "UPDATE motoristas SET status_motorista = :novoStatus WHERE id = :idMotorista";
    $statement = $pdo->prepare($queryAtualizarStatus);
    $statement->bindParam(':idMotorista', $idMotorista);
    $statement->bindParam(':novoStatus', $novoStatus, PDO::PARAM_INT);

    // Verifique se a atualização foi bem-sucedida
    if ($statement->execute()) {
        // A atualização foi bem-sucedida, você pode retornar uma resposta JSON ou outra saída aqui, se desejar
        $response = ['success' => true, 'message' => 'Status do motorista atualizado com sucesso.'];
        echo json_encode($response);
        exit;
    } else {
        // A atualização falhou, você pode retornar uma resposta de erro
        $response = ['success' => false, 'message' => 'Ocorreu um erro ao atualizar o status do motorista.'];
        echo json_encode($response);
        exit;
    }
} else {
    // Se o ID do motorista e o novo status não foram enviados, retorne uma resposta de erro
    $response = ['success' => false, 'message' => 'Parâmetros ausentes.'];
    echo json_encode($response);
    exit;
}