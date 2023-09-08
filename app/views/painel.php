<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
require_once('paginacao.php');
//print_r($_SESSION);
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header('Location: ../../');
    exit;
}
$idUsuarioLogado = $_SESSION['id'];
$tipoUsuarioLogado = $_SESSION['tipo_usuario'];
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

function buscarObservacoes($pdo, $idOcorrencia)
{
    $query = "SELECT o.*, u.usuario AS nome_usuario FROM observacoes o
              LEFT JOIN usuarios u ON o.id_usuario = u.id
              WHERE o.id_ocorrencia = :idOcorrencia
              ORDER BY o.data_registro DESC";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':idOcorrencia', $idOcorrencia);
    $statement->execute();
    return $statement->fetchAll(PDO::FETCH_ASSOC);
}

// Consulta SQL para selecionar todos os usuários
$queryuser = "SELECT id, nome, usuario, tipo_usuario FROM usuarios";
$statement = $pdo->prepare($queryuser);
$statement->execute();

// Recupere os resultados em um array
$usuarios = $statement->fetchAll(PDO::FETCH_ASSOC);


//var_dump($idUsuarioLogado);
//var_dump($_SESSION['usuario']);
//var_dump($_SESSION['tipo_usuario']);

?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>painel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="shortcut icon" href="../../assets/images/fav.png">
    <link rel="stylesheet" href="../../assets/css/painel.css">
</head>

<body>
    <main class="main">
        <header>
            <nav class="navbar navbar-expand-lg navbar-dark" aria-label="Tenth navbar example">
                <div class="container-fluid">
                    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample08" aria-controls="navbarsExample08" aria-expanded="false" aria-label="Toggle navigation">
                        <span class="navbar-toggler-icon"></span>
                    </button>
                    <h3 class="user-logado"><?php echo "Bem-Vindo: " . $_SESSION['usuario']; ?></h3>
                    <div class="collapse navbar-collapse justify-content-md-center" id="navbarsExample08">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <button class="nav-bottom"><a class="nav-link active" aria-current="page" href="painel">VOLTAR PAINEL</a></button>
                            </li>
                            <li class="nav-item">
                                <?php if ($tipoUsuarioLogado === 1) {
                                    echo ' <button class="nav-bottom"><a class="nav-link active" aria-current="page" href="painel" data-bs-toggle="modal" data-bs-target="#adduser">NOVO USUÁRIO</a></button>';
                                }
                                ?>
                            </li>
                            <li class="nav-item">
                                <?php if ($tipoUsuarioLogado === 1) {
                                    echo ' <button class="nav-bottom"><a class="nav-link active" aria-current="page" href="painel" data-bs-toggle="modal" data-bs-target="#allusers">USUÁRIOS REGISTRADOS</a></button>';
                                }
                                ?>
                            </li>
                            <li class="nav-item">
                                <button class="nav-bottom"><a class="nav-link active" aria-current="page" href="novaOcorrencia" data-bs-toggle="modal" data-bs-target="#addocorrencia">NOVA OCORRÊNCIA</a></button>
                            </li>
                            <li class="nav-item">
                                <?php
                                if ($tipoUsuarioLogado === 1) {
                                    echo '<button class="nav-bottom"><a class="nav-link" href="relatorio"  data-bs-toggle="modal" data-bs-target="#relatorios">RELATÓRIOS</a></button>';
                                }
                                ?>
                            </li>
                            <li class="nav-item">
                                <button class="nav-bottom"><a class="nav-link" href="logout">SAIR <ion-icon name="exit-outline"></ion-icon></a></button>
                            </li>
                        </ul>
                    </div>
                </div>
            </nav>
        </header>
        <section class="tabela-principal">
            <div class="header-tabela">
                <div class="titulo-painel">
                    <h1><b>Painel Principal</b></h1>
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
                        <th scope="col">DESCRIÇÃO COMPLETA</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($ocorrencias as $ocorrencia) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                        <tr>
                            <td><?php echo $ocorrencia['id']; ?></td>
                            <td><?php echo substr($ocorrencia['titulo'], 0, 20); ?></td>
                            <td>
                                <div class="descricao-ocorrencia">
                                    <div><?php echo substr($ocorrencia['descricao'], 0, 50) . " ..."; ?>
                            </td>
                            </div> <!-- Limitar a 100 caracteres -->
                            <td><?php echo $ocorrencia['local']; ?></td>
                            <td><?php echo $ocorrencia['nome_responsavel']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ocorrencia['data_registro'])); ?></td> <!-- Formata data e hora para dd/mm/aaaa H:i -->
                            <td>
                                <a class="btn-descricao" href="#" data-bs-toggle="modal" data-bs-target="#descricao_completa_<?php echo $ocorrencia['id']; ?>">
                                    Visualizar Descrição
                                    <ion-icon name="paper-plane-outline"></ion-icon></a>
                            </td>
                        </tr>
                        <!-- Modal DESCRIÇÃO COMPLETA -->
                        <div class="modal fade modaldescription" id="descricao_completa_<?php echo $ocorrencia['id']; ?>" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Descrição Completa</b></h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <?php echo $ocorrencia['descricao']; ?>
                                        <hr>
                                    </div>
                                    <div class="observacoes">
                                        <p><strong>Observações Adicionais:</strong></p>
                                        <!-- Aqui você pode exibir as observações relacionadas a esta ocorrência -->
                                        <div>
                                            <?php
                                            $idOcorrencia = $ocorrencia['id'];
                                            $observacoes = buscarObservacoes($pdo, $idOcorrencia); // Função para buscar observações no banco de dados
                                            foreach ($observacoes as $observacao) {
                                                echo "<div class='textoobservacao'><strong>" . $observacao['nome_usuario'] . "</strong>: " . $observacao['observacao'] . "</div>";
                                            }
                                            ?>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <div><b><?php echo $ocorrencia['nome_responsavel']; ?></b></div>
                                        <div><b><?php echo date('d/m/Y H:i', strtotime($ocorrencia['data_registro'])); ?></b></div>
                                        <!-- Botão para abrir o Modal de Adicionar Observação -->
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#adicionarObservacao_<?php echo $ocorrencia['id']; ?>">
                                            Adicionar Observação
                                        </button>

                                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- MODAL PARA ADICIONAR OBSERVAÇÕES -->
                        <div class="modal fade modaldescription" id="adicionarObservacao_<?php echo $ocorrencia['id']; ?>" tabindex="-1" aria-labelledby="adicionarObservacaoLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="adicionarObservacaoLabel"><b>Adicionar Observação</b></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Formulário para adicionar a observação -->
                                        <form action="processaObservacao.php" method="POST">
                                            <input type="hidden" name="ocorrencia_id" value="<?php echo $ocorrencia['id']; ?>">
                                            <div class="mb-3">
                                                <label for="observacao" class="form-label"><b>Observação:</b></label>
                                                <textarea class="form-control" id="observacao" name="observacao" rows="4" required></textarea>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="submit" id="cadastra_observacao" name="cadastra_observacao" class="btn btn-primary">Salvar Observação</button>
                                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                                            </div>
                                        </form>
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
                        <li class="page-item selected-button <?php echo ($pagina === $paginaAtual) ? 'active' : ''; ?>">
                            <a class="page-link" href="?page=<?php echo $pagina; ?>"><?php echo $pagina; ?></a>
                        </li>
                    <?php endfor; ?>

                    <!-- Botão "Next" -->
                    <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                        <a class="page-link" href="?page=<?php echo $paginaAtual + 1; ?>" aria-label="Avançar">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Modal ADICIONA NOVA OCORRENCIA -->
        <div class="modal fade" id="addocorrencia" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Adicionar Nova Ocorrência</b></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- COPO DO MODAL/FORMULARIO ADICIONAR NOVA OCORRENCIA -->
                    <div class="modal-body">
                        <form action="processaOcorrencia.php" method="POST">
                            <div class="col-md-6 mb-3">
                                <label for="titulo" class="form-label"><b>Título</b></label>
                                <input type="text" class="form-control" id="titulo" name="titulo" placeholder="Informe um pequeno título da ocorrência" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="local" class="form-label"><b>Local</b></label>
                                        <input type="text" class="form-control" id="local" name="local" placeholder="Informe o local do Ocorrido" required>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="descricao" class="form-label"><b>Relatório Da Ocorrência</b></label>
                                <textarea class="form-control" id="descricao" name="descricao" rows="3" placeholder="Relate a Ocorrência" required></textarea>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" name="cadastraOcorrencia" id="cadastraOcorrencia" class="btn btn-primary">Adicionar</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal ADICIONA NOVO USUARIO -->
        <div class="modal fade" id="adduser" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Adicionar Novo Usuário</b></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- CORPO DO MODAL/FORMULARIO ADICIONAR NOVO USUÁRIO -->
                    <div class="modal-body">
                        <div class="card-body">
                            <form method="POST" action="addusuario.php">
                                <div class="mb-3">
                                    <label for="nome" class="form-label"><b>Nome Completo</b></label>
                                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Digite Seu nome Completo" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="usuario" class="form-label"><b>Usuário</b></label>
                                            <input type="text" class="form-control" id="usuario" name="usuario" placeholder="EX: antonio.venturim" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="senha" class="form-label"><b>Senha</b></label>
                                            <input type="password" class="form-control" id="senha" name="senha" placeholder="Crie uma senha" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="tipo_usuario"><b>Tipo de Usuario</b></label>
                                    <select class="form-select w-25" name="tipo_usuario" id="tipo_usuario" required>
                                        <option value="" disabled selected>Selecione</option>
                                        <option value="0">Usuario</option>
                                        <option value="1">Administrador</option>
                                    </select>
                                </div>
                                <div>
                                    <button type="submit" id="adduser" name="adduser" class="btn btn-primary w-md">Cadastrar</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Modal TODOS OS USUARIO -->
        <div class="modal fade" id="allusers" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Usuarios Registrados</b></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- CORPO DO MODAL TODOS USUÁRIO -->
                    <div class="modal-body">
                        <table class="table-usuarios table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th scope="col">ID</th>
                                    <th scope="col">NOME COMPLETO</th>
                                    <th scope="col">USUARIO</th>
                                    <th scope="col">STATUS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($usuarios as $usuario) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                    <tr>
                                        <td><?php echo $usuario['id']; ?></td>
                                        <td><?php echo $usuario['nome']; ?></td>
                                        <td><?php echo $usuario['usuario']; ?></td>
                                        <td><?php echo ($usuario['tipo_usuario'] == 1 ? 'Administrador' : 'Usuário Normal') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="modal-footer">
                            <button type="submit" name="adduser" id="adduser" data-bs-toggle="modal" data-bs-target="#adduser" class="btn btn-primary">Adicionar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>


          <!-- Modal RELATÓRIOS -->
          <div class="modal fade" id="relatorios" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Relatórios</b></h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!-- CORPO DO MODAL RELATÓRIOS-->
                    <div class="modal-body">
                        <h1>EM DESENVOLVIMENTO</h1>
                    </div>
                </div>
            </div>
        </div>
    </main>
    <tfoot>

    </tfoot>

</body>

</html>