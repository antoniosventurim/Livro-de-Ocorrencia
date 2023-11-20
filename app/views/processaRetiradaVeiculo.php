<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

if (isset($_POST['cadretiradaveiculo'])) {
    $motoristaResponsavel = $_POST['usuarioResponsavel'];
    $nomeVeiculo = $_POST['nomeVeiculo'];
    $destinoVeiculo = $_POST['destino'];
    $dataRetirada = $_POST['dataRetirada'];
    $statusRetirada = $_POST['statusRetirada']; // Recupere o status da retirada

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id']; // Certifique-se de que $_SESSION['usuario'] contém o ID do usuário

    $queryVerificaRetirada = "SELECT COUNT(*) FROM retirada_veiculos WHERE (id_motorista = :id_motorista OR id_veiculo = :veiculo) AND statusVeiculo = 'ativa'";
    $statement = $pdo->prepare($queryVerificaRetirada);
    $statement->bindParam(':id_motorista', $motoristaResponsavel);
    $statement->bindParam(':veiculo', $nomeVeiculo);
    $statement->execute();
    $retiradaAtiva = $statement->fetchColumn();

    if ($retiradaAtiva > 0) {
        header('Location: painel.php?erro=1');
    } else {
        // O usuário não possui uma retirada ativa, permitir a nova retirada e registrá-la no banco de dados
        $queryInserirRetirada = "INSERT INTO retirada_veiculos (id_motorista, id_usuario, id_veiculo, destino, data_retirada, statusVeiculo, data_registro) VALUES (:usuarioResponsavel, :id_usuario, :veiculo, :destino, :dataRetirada, :statusRetirada, NOW())";
        $statement = $pdo->prepare($queryInserirRetirada);
        $statement->bindParam(':usuarioResponsavel', $motoristaResponsavel);
        $statement->bindParam(':id_usuario', $idUsuarioLogado); // Use o ID do usuário logado
        $statement->bindParam(':veiculo', $nomeVeiculo);
        $statement->bindParam(':destino', $destinoVeiculo);
        $statement->bindParam(':dataRetirada', $dataRetirada);
        $statement->bindParam(':statusRetirada', $statusRetirada); // Use o status da retirada
        $statement->execute();

        // Redirecionar de volta para a página do painel após a inserção com msg
        header('Location: painel.php?sucesso=1');
        exit;
    }
}




// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
