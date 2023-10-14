<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
header('Content-Type: text/html; charset=UTF-8');

if (isset($_POST['data_inicio']) || isset($_POST['data_fim']) || isset($_POST['titulo_ocorrencia']) || isset($_POST['nome_responsavel'])) {
    $query = "SELECT o.data_registro, o.titulo, u.nome AS nome_responsavel
              FROM ocorrencias o
              LEFT JOIN usuarios u ON o.id_responsavel = u.id
              WHERE 1 = 1";

    // Adicione filtros com base no que o usuário preencheu
    if (!empty($_POST['data_inicio']) && !empty($_POST['data_fim'])) {
        $query .= " AND o.data_registro BETWEEN :dataInicio AND :dataFim";
    }

    if (!empty($_POST['titulo_ocorrencia'])) {
        $query .= " AND o.titulo LIKE :tituloOcorrencia";
    }

    if (!empty($_POST['nome_responsavel'])) {
        $query .= " AND u.nome LIKE :nomeResponsavel";
    }

    $query .= " ORDER BY data_registro ASC";

    // Prepare a consulta
    $statement = $pdo->prepare($query);

    // Substitua os marcadores de posição pelos valores, se necessário
    if (!empty($_POST['data_inicio']) && !empty($_POST['data_fim'])) {
        $statement->bindParam(':dataInicio', $_POST['data_inicio']);
        $statement->bindParam(':dataFim', $_POST['data_fim']);
    }

    if (!empty($_POST['titulo_ocorrencia'])) {
        $tituloOcorrencia = '%' . $_POST['titulo_ocorrencia'] . '%'; // Adicione curingas para fazer uma pesquisa parcial
        $statement->bindParam(':tituloOcorrencia', $tituloOcorrencia, PDO::PARAM_STR); // Defina o tipo de dado como string (PDO::PARAM_STR)
    }

    if (!empty($_POST['nome_responsavel'])) {
        $nomeResponsavel = '%' . $_POST['nome_responsavel'] . '%'; // Adicione curingas para fazer uma pesquisa parcial
        $statement->bindParam(':nomeResponsavel', $nomeResponsavel, PDO::PARAM_STR); // Defina o tipo de dado como string (PDO::PARAM_STR)
    }

    // Execute a consulta
    $statement->execute();
}

$result = $statement->fetchAll(PDO::FETCH_ASSOC);

// Se você tiver resultados, pode gerar a tabela ou os dados no formato desejado
if (count($result) > 0) {
    // Você pode gerar a tabela ou retornar os dados no formato desejado
    // Aqui, vou gerar uma tabela como exemplo

    echo '<table class="table table-bordered table-striped tabelafiltradaOcorrencias">';
    echo "<tr>";
    echo "<th>Data de Registro</th>";
    echo "<th>Título da Ocorrência</th>";
    echo "<th>Nome do Responsável</th>";
    echo "</tr>";

    foreach ($result as $ocorrencia) {
        echo "<tr>";
        echo "<td>" . date('d/m/Y', strtotime($ocorrencia['data_registro'])) . "</td>";
        echo "<td>" . $ocorrencia['titulo'] . "</td>";
        echo "<td>" . $ocorrencia['nome_responsavel'] . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Nenhuma ocorrência encontrada.";
}
