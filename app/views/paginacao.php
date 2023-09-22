<?php
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    // Redirecionar para a página de login se não estiver logado
    header('Location: painel');
    exit;
}else{
}

function buscarOcorrenciasPaginadas($pdo, $pagina, $ocorrenciasPorPagina)
{
    $inicio = ($pagina - 1) * $ocorrenciasPorPagina;

    $query = "SELECT o.*, u.usuario AS nome_responsavel FROM ocorrencias o LEFT JOIN usuarios u ON o.id_responsavel = u.id ORDER BY o.id DESC LIMIT $inicio, $ocorrenciasPorPagina";

    $statement = $pdo->query($query);
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

function calcularTotalOcorrencias($pdo)
{
    // Consulta SQL para contar o número total de ocorrências
    $queryTotalOcorrencias = "SELECT COUNT(*) AS total_ocorrencias FROM ocorrencias";
    $statementTotalOcorrencias = $pdo->query($queryTotalOcorrencias);

    return $statementTotalOcorrencias->fetchColumn();
}

function exibirNavegacaoPaginacao($totalOcorrencias, $ocorrenciasPorPagina, $paginaAtual)
{
    // Calcule o número total de páginas
    $totalPaginas = ceil($totalOcorrencias / $ocorrenciasPorPagina);

    // Exiba links para as páginas anteriores e seguintes
    echo '<div class="pagination">';
    if ($paginaAtual > 1) {
        echo '<a href="?page=' . ($paginaAtual - 1) . '">Anterior</a>';
    }

    for ($i = 1; $i <= $totalPaginas; $i++) {
        if ($i === $paginaAtual) {
            echo '<span class="current">' . $i . '</span>';
        } else {
            echo '<a href="?page=' . $i . '">' . $i . '</a>';
        }
    }

    if ($paginaAtual < $totalPaginas) {
        echo '<a href="?page=' . ($paginaAtual + 1) . '">Próxima</a>';
    }
    echo '</div>';
}
