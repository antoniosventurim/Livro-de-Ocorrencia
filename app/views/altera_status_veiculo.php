<?php
// Arquivo verificar_local.php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

if (isset($_POST['id_veiculo']) && isset($_POST['novo_status'])) {
    $idVeiculo = $_POST['id_veiculo'];
    $novoStatus = $_POST['novo_status'];

    // Prepare e execute a consulta SQL para atualizar o status do motorista
    $queryAtualizarStatus = "UPDATE veiculos SET status_veiculo = :novoStatus WHERE id = :idVeiculo";
    $statement = $pdo->prepare($queryAtualizarStatus);
    $statement->bindParam(':idVeiculo', $idVeiculo);
    $statement->bindParam(':novoStatus', $novoStatus, PDO::PARAM_INT);

    // Verifique se a atualização foi bem-sucedida
    if ($statement->execute()) {
        // A atualização foi bem-sucedida, você pode retornar uma resposta JSON ou outra saída aqui, se desejar
        $response = ['success' => true, 'message' => 'Status do veículo atualizado com sucesso.'];
        echo json_encode($response);
        exit;
    } else {
        // A atualização falhou, você pode retornar uma resposta de erro
        $response = ['success' => false, 'message' => 'Ocorreu um erro ao atualizar o status do veículo.'];
        echo json_encode($response);
        exit;
    }
} else {
    // Se o ID do motorista e o novo status não foram enviados, retorne uma resposta de erro
    $response = ['success' => false, 'message' => 'Parâmetros ausentes.'];
    echo json_encode($response);
    exit;
}