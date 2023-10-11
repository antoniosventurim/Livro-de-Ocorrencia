<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verificar se o usuário está logado
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: index.php');
    exit;
}

if (isset($_POST['cadastraEvento'])) {
    $nomeEvento = $_POST['nomeEvento'];
    $solicitante = $_POST['solicitante'];
    $localReservado = $_POST['localReservado'];
    $diaSemana = $_POST['diaSemana'];
    $dataInicio = $_POST['dataHoraEventoinicio'];
    $dataFim = $_POST['dataHoraEventofim'];
    $qtdParticipantes = $_POST['qtdParticipantes'];

    // Obtém o ID do usuário logado da variável de sessão
    $idUsuarioLogado = $_SESSION['id'];

    // Consulta para verificar se já existe um evento no mesmo local e horário
    $queryVerificaEvento = "SELECT COUNT(*) FROM eventos WHERE local_reservado = :localReservado AND data_inicio <= :dataFim AND data_fim >= :dataInicio";
    $statementVerificaEvento = $pdo->prepare($queryVerificaEvento);
    $statementVerificaEvento->bindParam(':localReservado', $localReservado);
    $statementVerificaEvento->bindParam(':dataInicio', $dataInicio);
    $statementVerificaEvento->bindParam(':dataFim', $dataFim);
    $statementVerificaEvento->execute();
    $numeroDeEventos = $statementVerificaEvento->fetchColumn();

    if ($numeroDeEventos > 0) {
        // Já existe um evento no mesmo local e horário, exiba uma mensagem de erro ou tome a ação apropriada.
        echo "Já existe um evento no mesmo local e horário.";
    } else {
        // Processa o formulário e insere a ocorrência no banco de dados
        $queryInserirOcorrencia = "INSERT INTO eventos (nome_evento, solicitante, local_reservado, dia_semana, data_inicio, data_fim, qtd_participantes, usuario_id, data_registro) VALUES (:nomeEvento, :solicitante, :localReservado, :diaSemana, :dataInicio, :dataFim, :qtdParticipantes, :id_usuario, NOW())";
        $statement = $pdo->prepare($queryInserirOcorrencia);
        $statement->bindParam(':nomeEvento', $nomeEvento);
        $statement->bindParam(':solicitante', $solicitante);
        $statement->bindParam(':localReservado', $localReservado);
        $statement->bindParam(':diaSemana', $diaSemana);
        $statement->bindParam(':dataInicio', $dataInicio);
        $statement->bindParam(':dataFim', $dataFim);
        $statement->bindParam(':qtdParticipantes', $qtdParticipantes);
        $statement->bindParam(':id_usuario', $idUsuarioLogado); // Use o ID do usuário logado
        $statement->execute();

        // Redirecionar de volta para a página do painel após a inserção
        header('Location: painel.php');
        exit;
    }
}

// Se o formulário não foi enviado ou ocorreu algum erro, você pode adicionar tratamento de erro aqui
// ...
