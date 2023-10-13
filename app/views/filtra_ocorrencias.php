<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
header('Content-Type: text/html; charset=UTF-8');

if (isset($_POST['data_inicio']) && isset($_POST['data_fim']) && isset($_POST['titulo_ocorrencia']) && isset($_POST['nome_responsavel'])) {
    $dataInicio = $_POST['data_inicio'];
    $dataFim = $_POST['data_fim'];
    $tituloOcorrencia = $_POST['titulo_ocorrencia'];
    $nomeResponsavel = $_POST['nome_responsavel'];

    // Converte as datas do formato "d-m-Y" para "Y-m-d"
    $dataInicio = date('Y-m-d', strtotime($dataInicio));
    $dataFim = date('Y-m-d', strtotime($dataFim));

    $query = "SELECT o.data_registro, o.titulo, u.nome AS nome_responsavel, o.descricao
              FROM ocorrencias o
              LEFT JOIN usuarios u ON o.id_responsavel = u.id
              WHERE 1 = 1";

    // Adicione filtros com base no que o usuário preencheu
    if (!empty($dataInicio)) {
        $query .= " AND o.data_registro >= :dataInicio";
    }

    if (!empty($dataFim)) {
        $dataFim = date('Y-m-d', strtotime($dataFim . ' + 1 day')); // Adicione 1 dia à data de término
        $query .= " AND o.data_registro <= :dataFim";
    }

    if (!empty($tituloOcorrencia)) {
        $query .= " AND o.titulo LIKE :tituloOcorrencia";
    }

    if (!empty($nomeResponsavel)) {
        $query .= " AND u.nome LIKE :nomeResponsavel";
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

    if (!empty($tituloOcorrencia)) {
        $tituloOcorrencia = '%' . $tituloOcorrencia . '%'; // Adicione curingas para fazer uma pesquisa parcial
        $statement->bindParam(':tituloOcorrencia', $tituloOcorrencia);
    }

    if (!empty($nomeResponsavel)) {
        $nomeResponsavel = '%' . $nomeResponsavel . '%'; // Adicione curingas para fazer uma pesquisa parcial
        $statement->bindParam(':nomeResponsavel', $nomeResponsavel);
    }

    // Execute a consulta
    $statement->execute();

    // Recupere os resultados
    $result = $statement->fetchAll(PDO::FETCH_ASSOC);

    // Se você tiver resultados, pode gerar a tabela ou os dados em JSON, por exemplo
    if (count($result) > 0) {
        // Você pode gerar a tabela ou retornar os dados no formato desejado
        // Aqui, vou gerar uma tabela como exemplo

        echo '<table class="table table-bordered table-striped tabelafiltrada">';
        echo "<tr>";
        echo "<th>Data de Registro</th>";
        echo "<th>Título da Ocorrência</th>";
        echo "<th>Nome do Responsável</th>";
        echo "<th>Descrição</th>";
        echo "</tr>";

        foreach ($result as $ocorrencia) {
            echo "<tr>";
            echo "<td>" . date('d/m/Y', strtotime($ocorrencia['data_registro'])) . "</td>";
            echo "<td>" . $ocorrencia['titulo'] . "</td>";
            echo "<td>" . $ocorrencia['nome_responsavel'] . "</td>";
            echo "<td>" . $ocorrencia['descricao'] . "</td>";
            echo "</tr>";
        }

        echo "</table>";
    } else {
        echo "Nenhuma ocorrência encontrada.";
    }
} else {
    echo " ";
}
?>
