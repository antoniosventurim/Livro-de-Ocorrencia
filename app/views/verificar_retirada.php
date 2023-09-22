<?php
// Inclua seus arquivos de configuração e conexão com o banco de dados aqui
require_once(__DIR__ . '/../../includes/db.php');
// Certifique-se de que recebeu um valor válido do motoristaResponsavel (via POST)
if (isset($_POST['usuarioResponsavel']) && !empty($_POST['usuarioResponsavel'])) {
    // Obtenha o valor do motoristaResponsavel do POST
    $motoristaResponsavel = $_POST['usuarioResponsavel'];

    // Consulta SQL para verificar se o motorista possui uma retirada ativa
    $queryVerificaRetirada = "SELECT COUNT(*) FROM retirada_veiculos WHERE id_motorista = :id_motorista AND statusVeiculo = 'ativa'";
    $statement = $pdo->prepare($queryVerificaRetirada);
    $statement->bindParam(':id_motorista', $motoristaResponsavel);
    $statement->execute();
    $retiradaAtiva = $statement->fetchColumn();

    // Crie um array associativo para a resposta JSON
    $resposta = array(
        'retiradaAtiva' => ($retiradaAtiva > 0) // Se > 0, o motorista possui uma retirada ativa
    );

    // Defina o cabeçalho como JSON
    header('Content-Type: application/json');

    // Retorne a resposta como JSON
    echo json_encode($resposta);
} else {
    // Se motoristaResponsavel não foi fornecido, retorne um erro
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(array('erro' => 'Parâmetro inválido'));
}
?>
