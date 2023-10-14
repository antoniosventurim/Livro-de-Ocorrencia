<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
header('Content-Type: text/html; charset=UTF-8');


$nomePessoa = '%' . $_POST['busca_nome_motorista'] . '%';

$query = "SELECT m.nome AS nome_motorista, v.nome AS nome_veiculo, r.destino, r.data_retirada, d.data_devolucao
FROM retirada_veiculos r
JOIN motoristas m ON r.id_motorista = m.id
JOIN devolucoes d ON r.id_data_devolucao = d.id
JOIN veiculos v ON r.id_veiculo = v.id
WHERE m.nome LIKE :nomePessoa
ORDER BY r.data_retirada ASC";

$statement = $pdo->prepare($query);
$statement->bindValue(':nomePessoa', $nomePessoa, PDO::PARAM_STR);
$statement->execute();

$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if (count($result) > 0) {
    echo '<table class="table table-bordered table-striped tabelafiltradaAcessos">';
    echo "<tr>";
    echo "<th>NOME</th>";
    echo "<th>VEÍCULO</th>";
    echo "<th>DESTINO</th>";
    echo "<th>DATA DE RETIRADA</th>";
    echo "<th>ID DATA DEVOLUÇÃO</th>";
    echo "</tr>";

    foreach ($result as $retirada) {
        echo "<tr>";
        echo "<td>" . $retirada['nome_motorista'] . "</td>";
        echo "<td>" . $retirada['nome_veiculo'] . "</td>";
        echo "<td>" . $retirada['destino'] . "</td>";
        echo "<td>" . date('d/m/Y h:m', strtotime($retirada['data_retirada'])) . "</td>";
        echo "<td>" . date('d/m/Y h:m', strtotime($retirada['data_devolucao'])) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Nenhuma Retirada de Chave Encontrada.";
}
