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

$numPaginasVisiveis = 5; // Defina o número de páginas visíveis desejado
$numPaginasAntesDepois = floor($numPaginasVisiveis / 2);
$inicio = max(1, $paginaAtual - $numPaginasAntesDepois);
$fim = min($totalPaginas, $inicio + $numPaginasVisiveis - 1);

//busca no banco de dados as ocorrencias por ID
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <link rel="stylesheet" href="../../assets/css/painel2.css">
    <link rel="shortcut icon" href="../../assets/images/fav.png">
    <title>Painel</title>
</head>

<body>
    <div class="main">
        <main class="d-flex flex-nowrap side-bar">
            <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark" style="width: 280px;">
                <a href="/" class="d-flex logo align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <span>PORTARIA DIGITAL</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="http://localhost/portaria/app/views/painel2" class="nav-link text-white" aria-current="page">
                            <i class="bi bi-house-fill">
                                <use xlink:href="#hom"></use>
                            </i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <?php if ($tipoUsuarioLogado === 1) {
                            echo '<a href="painel2" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#adduserr">
                            <i class="bi bi-person-add">
                            <use xlink:href="#hom"></use>
                        </i>
                            Novo Usuario
                        </a>';
                        } ?>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#alluserss">
                            <i class="bi bi-people-fill"></i>
                            Usuarios Registrados
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white">
                            <i class="bi bi-browser-safari"></i>
                            Adicionar Novo Local
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#addocorrenciaa">
                            <i class="bi bi-journal-plus"></i>
                            Nova Ocorrencia
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#addocorrenciaa">
                            <i class="bi bi-key"></i>
                            Retirada de Chave
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#relatorioss">
                            <i class="bi bi-file-earmark-pdf"></i>
                            Relatórios
                        </a>
                    </li>
                </ul>
                <hr>
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="../../assets/images/fav.png" alt="" width="32" height="32" class="rounded-circle me-2">
                        <strong class="user-logado"><?php echo $_SESSION['usuario']; ?></strong>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                        <li class="d-flex ml-5 align-items-center logout-user"><a class="dropdown-item" href="logout.php"><svg class="svg-logo" xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-box-arrow-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M10 12.5a.5.5 0 0 1-.5.5h-8a.5.5 0 0 1-.5-.5v-9a.5.5 0 0 1 .5-.5h8a.5.5 0 0 1 .5.5v2a.5.5 0 0 0 1 0v-2A1.5 1.5 0 0 0 9.5 2h-8A1.5 1.5 0 0 0 0 3.5v9A1.5 1.5 0 0 0 1.5 14h8a1.5 1.5 0 0 0 1.5-1.5v-2a.5.5 0 0 0-1 0v2z" />
                                    <path fill-rule="evenodd" d="M15.854 8.354a.5.5 0 0 0 0-.708l-3-3a.5.5 0 0 0-.708.708L14.293 7.5H5.5a.5.5 0 0 0 0 1h8.793l-2.147 2.146a.5.5 0 0 0 .708.708l3-3z" />
                                </svg>Sair</a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="table-info">
                <div class="tabela-principal">
                    <table class="table">
                        <thead>
                            <tr>
                                <th scope="col">TÍTULO</th>
                                <th scope="col">LOCAL</th>
                                <th scope="col">RESPONSÁVEL</th>
                                <th scope="col">DATA REGISTRO</th>
                                <th scope="col">DESCRIÇÃO COMPLETA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($ocorrencias as $ocorrencia) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                <tr>
                                    <td><?php echo substr($ocorrencia['titulo'], 0, 20); ?></td>
                                    <!-- Limitar a 100 caracteres -->
                                    <td><?php echo substr($ocorrencia['local'], 0, 20); ?></td>
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
                                            <div class="modal-body bodydescription">
                                                <?php echo $ocorrencia['descricao']; ?>

                                                <hr>
                                                <div><b><?php echo $ocorrencia['nome_responsavel']; ?></b></div>
                                                <div><b><?php echo date('d/m/Y H:i', strtotime($ocorrencia['data_registro'])); ?></b></div>
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
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <div class="table-footer">
                    <div class="totalfooter">
                        <h1>Total Ocorrências: <?php echo $totalOcorrencias ?></h1>
                    </div>

                    <!-- Paginação -->
                    <nav class="paginacao" aria-label="Navegação de Páginas">
                        <ul class="pagination">
                            <!-- Botão "Anterior" -->
                            <li class="page-item <?php echo ($paginaAtual === 1) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $paginaAtual - 1; ?>" aria-label="Anterior">
                                    <span aria-hidden="true">Voltar</span>
                                </a>
                            </li>

                            <?php
                            for ($pagina = 1; $pagina <= $totalPaginas; $pagina++) :
                            ?>
                                <?php if ($pagina >= $inicio && $pagina <= $fim) : ?>
                                    <li class="page-item <?php
                                                            echo ($pagina === $paginaAtual) ? 'active' : '';
                                                            ?>">
                                        <a class="page-link" href="?page=<?php echo $pagina; ?>"><?php echo $pagina; ?></a>
                                    </li>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <!-- Botão "Proximo" -->
                            <li class="page-item <?php echo ($paginaAtual >= $totalPaginas) ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $paginaAtual + 1; ?>" aria-label="Próxima">
                                    <span aria-hidden="true">Avancar</span>
                                </a>
                            </li>
                        </ul>
                    </nav>

                    <!-- Modal ADICIONA NOVA OCORRENCIA -->
                    <div class="modal fade" id="addocorrenciaa" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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

                </div>
            </div>
        </main>
    </div>
    </div>
    <!-- Modal ADICIONA NOVO USUARIO -->
    <div class="modal fade" id="adduserr" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                                        <span id="usuarioValidationMessage"></span>
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
                    <div id="erroCadastroUsuario" class="alert alert-danger" style="display: none;"></div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal TODOS OS USUARIO -->
    <div class="modal fade" id="alluserss" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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
                        <button type="submit" name="adduser" id="adduser" data-bs-toggle="modal" data-bs-target="#adduserr" class="btn btn-primary">Adicionar</button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal RELATÓRIOS -->
    <div class="modal fade" id="relatorioss" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
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




    <script>
        const usuarioInput = document.getElementById("usuario");
        const usuarioValidationMessage = document.getElementById("usuarioValidationMessage");

        usuarioInput.addEventListener("input", function() {
            const usuario = usuarioInput.value;

            // Enviar uma solicitação AJAX para verificar_usuario.php
            fetch("verificar_usuario.php", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/x-www-form-urlencoded"
                    },
                    body: `usuario=${encodeURIComponent(usuario)}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        usuarioValidationMessage.textContent = "Nome de usuário disponível.";
                        usuarioValidationMessage.style.color = "green";
                    } else {
                        usuarioValidationMessage.textContent = "Nome de usuário já está em uso.";
                        usuarioValidationMessage.style.color = "red";
                    }
                })
                .catch(error => {
                    console.error("Erro na solicitação AJAX: " + error);
                });
        });
    </script>
</body>

</html>