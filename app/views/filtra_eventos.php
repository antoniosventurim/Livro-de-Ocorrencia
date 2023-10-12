<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

// Verifique se as datas foram fornecidas no POST
if (isset($_POST['data_inicio']) && isset($_POST['data_fim'])) {
    // Obtenha as datas de início e término do POST
    $dataInicio = $_POST['data_inicio'];
    $dataFim = $_POST['data_fim'];

    // Consulta SQL para buscar eventos com base nas datas
    $query = "SELECT data_inicio, data_fim, nome_evento FROM eventos WHERE data_inicio >= :dataInicio AND data_fim <= :dataFim";

    // Prepare a consulta
    $statement = $pdo->prepare($query);

    // Substitua os marcadores de posição pelos valores
    $statement->bindParam(':dataInicio', $dataInicio);
    $statement->bindParam(':dataFim', $dataFim);

    // Execute a consulta
    $statement->execute();

    // Processar os resultados
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Aqui, você pode formatar os resultados como desejar. Neste exemplo, vou simplesmente exibir os eventos em uma lista.
    echo "<ul>";
    foreach ($result as $evento) {
        echo "<li>" . $evento['nome_evento'] . " - " . $evento['data_inicio'] . " - " . $evento['data_fim'] . "</li>";
    }
    echo "</ul>";
} else {
    echo "Datas de início e término não fornecidas no POST.";
}
