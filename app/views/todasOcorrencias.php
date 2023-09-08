<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
require_once('paginacao.php');
//print_r($_SESSION);
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header('Location: login');
    exit;
}
$idUsuarioLogado = $_SESSION['id'];
// Consulta SQL para obter o nome do usuário com base no ID armazenado na sessão
$queryNomeUsuario = "SELECT usuario FROM usuarios WHERE id = :idUsuario";
$statementNomeUsuario = $pdo->prepare($queryNomeUsuario);
$statementNomeUsuario->bindParam(':idUsuario', $_SESSION['usuario']);
$statementNomeUsuario->execute();
$nomeDoUsuario = $statementNomeUsuario->fetchColumn();

// Consulta SQL para selecionar as 10 últimas ocorrências ordenadas pela data de registro em ordem decrescente
$query = "SELECT o.*, u.usuario AS nome_responsavel FROM ocorrencias o LEFT JOIN usuarios u ON o.id_responsavel = u.id ORDER BY o.id DESC LIMIT 10"; // Essa consulta traz o nome do responsavél ao inves do ID nesse caso podemos colocar o nome do responsavél ao inves do ID
$statement = $pdo->prepare($query);
$statement->execute();
$ocorrencias = $statement->fetchAll(PDO::FETCH_ASSOC);



// Consulta SQL para contar o número total de ocorrências na tabela
$queryTotalOcorrencias = "SELECT COUNT(*) AS total_ocorrencias FROM ocorrencias";
$statementTotalOcorrencias = $pdo->query($queryTotalOcorrencias);
$totalOcorrencias = $statementTotalOcorrencias->fetchColumn();

// Calcular quantas ocorrências excedem o limite de 10 ocorrências
$limiteExcedente = max(0, $totalOcorrencias - 10);


// Defina o número de ocorrências por página
$ocorrenciasPorPagina = 10;

// Determine a página atual com base no parâmetro 'page' na URL
$paginaAtual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Busque as ocorrências paginadas
$ocorrencias = buscarOcorrenciasPaginadas($pdo, $paginaAtual, $ocorrenciasPorPagina);

// Calcule o número total de ocorrências
$totalOcorrencias = calcularTotalOcorrencias($pdo);
$totalPaginas = ceil($totalOcorrencias / $ocorrenciasPorPagina);


//var_dump($idUsuarioLogado);
//var_dump($_SESSION['usuario']);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>painel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./assets/css/painel.css">
</head>

<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark" aria-label="Tenth navbar example">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <h3 class="user-logado"><?php echo "Usuário: " . $_SESSION['usuario']; ?></h3>
                <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <button class="nav-bottom"><a class="nav-link active" aria-current="page" href="painel">VOLTAR PAINEL</a></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-bottom"><a class="nav-link active" aria-current="page" href="novaOcorrencia" data-bs-toggle="modal" data-bs-target="#staticBackdrop">ADICIONAR NOVA OCORRÊNCIA</a></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-bottom"><a class="nav-link" href="todasOcorrencias">TODAS OCORRÊNCIAS</a></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-bottom"><a class="nav-link" href="relatorio">RELATÓRIOS</a></button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-bottom"><a class="nav-link" href="logout">SAIR</a></button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>
    <main>
        <section class="tabela-principal">
            <div class="header-tabela">
                <div>
                    <h1>Painel Principal</h1>
                </div>
                <div>
                    <h4>TOTAL OCORRÊNCIAS: <?php echo $totalOcorrencias ?></h4>
                </div>
            </div>
            <table class="table table-striped-columns table-dark">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">TÍTULO</th>
                        <th scope="col">RELATÓRIO DA OCORRÊNCIA</th>
                        <th scope="col">LOCAL</th>
                        <th scope="col">RESPONSÁVEL</th>
                        <th scope="col">DATA REGISTRO</th>
                        <th scope="col">MAIS ITEMS</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ocorrencias as $ocorrencia) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                        <tr>
                            <td><?php echo $ocorrencia['id']; ?></td>
                            <td><?php echo substr($ocorrencia['titulo'], 0, 20); ?></td>
                            <td><?php echo substr($ocorrencia['descricao'], 0, 50); ?><a href="#" data-bs-toggle="modal" data-bs-target="#descricao_completa_<?php echo $ocorrencia['id']; ?>">...Descrição Completa</a></td> <!-- Limitar a 100 caracteres -->
                            <td><?php echo $ocorrencia['local']; ?></td>
                            <td><?php echo $ocorrencia['nome_responsavel']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ocorrencia['data_registro'])); ?></td> <!-- Formata data e hora para dd/mm/aaaa H:i -->
                            <td>Editar/Visualizar</td>
                        </tr>
                        <!-- Modal ADICIONA NOVA OCORRENCIA -->
                        <div class="modal fade modaldescription" id="descricao_completa_<?php echo $ocorrencia['id']; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Descrição Completa</h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo $ocorrencia['descricao']; ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
        <nav class="paginacao-todas" aria-label="Navegação de Páginas">
            <ul class="pagination">
                <?php if ($totalPaginas > 1) : ?>
                    <!-- Botão "Previous" -->
                    <li class="page-item <?php echo ($paginaAtual === 1) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $paginaAtual - 1; ?>" aria-label="Voltar">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>

                    <?php for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) : ?>
                        <!-- Página atual (com classe "active") -->
                        <li class="page-item <?php echo ($pagina === $paginaAtual) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $pagina; ?>"><?php echo $pagina; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Botão "Next" -->
                    <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $paginaAtual + 1; ?>" aria-label="Proximo">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Modal ADICIONA NOVA OCORRENCIA -->
        <div class="modal fade" id="staticBackdrop" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel">Adicionar nova ocorrência</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- COPO DO MODAL/FORMULARIO ADICIONAR NOVA OCORRENCIA -->
                    <div class="modal-body">
                        <form action="processaOcorrencia.php" method="POST">
                            <div class="col-md-6 mb-3">
                                <label for="formrow-firstname-input" class="form-label">Título</label>
                                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Informe um pequeno título da ocorrência">
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="formrow-email-input" class="form-label">Local</label>
                                        <input type="text" class="form-control" id="local" name="local" placeholder="Informe o local do Ocorrido">
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="exampleFormControlTextarea1" class="form-label">Relatório Da Ocorrência</label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Relate a Ocorrência"></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                                <button type="submit" name="cadastraOcorrencia" id="cadastraOcorrencia" class="btn btn-primary">Adicionar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <tfoot>

    </tfoot>

</body>

</html>