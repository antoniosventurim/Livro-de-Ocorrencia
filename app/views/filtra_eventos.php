<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
header('Content-Type: text/html; charset=UTF-8');
// Verifique se as datas foram fornecidas no POST
if (isset($_POST['data_inicio']) && isset($_POST['data_fim'])  && isset($_POST['busca_nome_evento'])) {
    $dataInicio = $_POST['data_inicio'];
    $dataFim = $_POST['data_fim'];
    $nomeEvento = $_POST['busca_nome_evento'];

    $query = "SELECT nome_evento, data_inicio, data_fim FROM eventos WHERE 1 = 1";

    // Adicione filtros com base no que o usuário preencheu
    if (!empty($dataInicio)) {
        $query .= " AND data_inicio >= :dataInicio";
    }

    if (!empty($dataFim)) {
        $query .= " AND data_fim <= :dataFim";
    }

    if (!empty($nomeEvento)) {
        $query .= " AND nome_evento LIKE :nomeEvento";
    }

    // Prepare a consulta
    $statement = $pdo->prepare($query);

    // Substitua os marcadores de posição pelos valores, se necessário
    if (!empty($dataInicio)) {
        $statement->bindParam(':dataInicio', $dataInicio);
    }

    if (!empty($dataFim)) {
        $statement->bindParam(':dataFim', $dataFim);
    }

    if (!empty($nomeEvento)) {
        $nomeEvento = '%' . $nomeEvento . '%'; // Adicione curingas para fazer uma pesquisa parcial
        $statement->bindParam(':nomeEvento', $nomeEvento);
    }

    // Execute a consulta
    $statement->execute();

    // Recupere os resultados
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);
    if (count($result) > 0) {
        echo '<table class="table table-bordered table-striped tabelafiltradaEventos">';
        echo "<tr>";
        echo "<th>Nome do Evento</th>";
        echo "<th>Data de Início</th>";
        echo "<th>Data de Término</th>";
        echo "</tr>";

        foreach ($result as $evento) {
            echo "<tr>";
            echo "<td>" . $evento['nome_evento'] . "</td>";
            echo "<td>" . date('d/m/Y h:m', strtotime($evento['data_inicio'])) . "</td>";
            echo "<td>" . date('d/m/Y h:m', strtotime($evento['data_fim'])) . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "Nenhum evento encontrado.";
    }
} else {
    echo "";
}
