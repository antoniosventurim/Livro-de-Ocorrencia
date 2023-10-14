<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
header('Content-Type: text/html; charset=UTF-8');

$nomePessoa = '%' . $_POST['busca_nome_pessoa'] . '%';

$query = "SELECT nome, destino, tipo_pessoa, data_acesso FROM acessos WHERE nome LIKE :nomePessoa ORDER BY data_acesso ASC";
$statement = $pdo->prepare($query);
$statement->bindParam(':nomePessoa', $nomePessoa, PDO::PARAM_STR);

$statement->execute();

$result = $statement->fetchAll(PDO::FETCH_ASSOC);
if (count($result) > 0) {
    echo '<table class="table table-bordered table-striped tabelafiltradaAcessos">';
    echo "<tr>";
    echo "<th>NOME</th>";
    echo "<th>DESTINO</th>";
    echo "<th>TIPO DE ACESSO</th>";
    echo "<th>DATA DE ACESSO</th>";
    echo "</tr>";

    foreach ($result as $acesso) {
        echo "<tr>";
        echo "<td>" . $acesso['nome'] . "</td>";
        echo "<td>" . $acesso['destino'] . "</td>";
        echo "<td>";
        if ($acesso['tipo_pessoa'] == 0) {
            echo 'Aluno';
        } elseif ($acesso['tipo_pessoa'] == 1) {
            echo 'Professor';
        } elseif ($acesso['tipo_pessoa'] == 2) {
            echo 'Visitante';
        } else {
            // Lida com qualquer outro valor, se necessário.
            echo 'Outro';
        }
        echo "</td>";
        echo "<td>" . date('d/m/Y h:m', strtotime($acesso['data_acesso'])) . "</td>";
        echo "</tr>";
    }

    echo "</table>";
} else {
    echo "Nenhum acesso encontrado.";
}
