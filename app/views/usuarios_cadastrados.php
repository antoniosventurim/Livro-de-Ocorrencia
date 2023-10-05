<?php
session_start();
require_once(__DIR__ . '/../../includes/db.php');
//print_r($_SESSION);
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header('Location: ../../');
    exit;
}

// Se o tipo de usuário não for 1, redirecione para uma página de acesso negado
if ($_SESSION['tipo_usuario'] != 1) {
    header('Location: painel.php');
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
$queryuser = "SELECT id, nome, usuario, tipo_usuario, status_usuario FROM usuarios";
$statement = $pdo->prepare($queryuser);
$statement->execute();

// Recupere os resultados em um array
$usuarios = $statement->fetchAll(PDO::FETCH_ASSOC);

$queryObservacoes = "SELECT obs.id AS observacao_id,
obs.id_ocorrencia AS id_ocorrencia,
obs.observacao,
obs.data_registro,
u.id AS usuario_id,
u.nome AS nome_usuario,
o.titulo AS titulo_ocorrencia,
o.descricao AS descricao_ocorrencia,
o.local AS local_ocorrencia
FROM observacoes AS obs
INNER JOIN usuarios AS u ON obs.id_usuario = u.id
INNER JOIN ocorrencias AS o ON obs.id_ocorrencia = o.id
ORDER BY obs.data_registro DESC
LIMIT 10;";
$statement = $pdo->prepare($queryObservacoes);
$statement->execute();
$totalObservacoes = $statement->fetchAll(PDO::FETCH_ASSOC);

// Query Retorna os Motoristas
$queryMotoristas = "SELECT id, nome, status_motorista, setor FROM motoristas";
$statement = $pdo->prepare($queryMotoristas);
$statement->execute();
$motoristas = $statement->fetchAll(PDO::FETCH_ASSOC);

//Query Retorna Veiculos
$queryVeiculos = "SELECT id, nome, tipo_veiculo, placa, status_veiculo FROM veiculos";
$statement = $pdo->prepare($queryVeiculos);
$statement->execute();
$veiculos = $statement->fetchAll(PDO::FETCH_ASSOC);

//Query Retorna reitada de veiculos
$queryRetiradaVeiculos = "SELECT usuarios.nome AS nome_usuario, motoristas.nome AS nome_motorista, veiculos.nome AS nome_veiculo, retirada_veiculos.data_retirada, retirada_veiculos.destino, retirada_veiculos.id_data_devolucao, retirada_veiculos.id, devolucoes.data_devolucao FROM retirada_veiculos INNER JOIN usuarios ON retirada_veiculos.id_usuario = usuarios.id INNER JOIN motoristas ON retirada_veiculos.id_motorista = motoristas.id INNER JOIN veiculos ON retirada_veiculos.veiculo = veiculos.id LEFT JOIN devolucoes ON retirada_veiculos.id = devolucoes.id_retirada_veiculo ORDER BY retirada_veiculos.id DESC LIMIT 5;";
$statement = $pdo->prepare($queryRetiradaVeiculos);
$statement->execute();
$retiradaVeiculos = $statement->fetchAll(PDO::FETCH_ASSOC);

//Query Retorna locais
$queryLocais = "SELECT nome_local, bloco FROM locais";
$statement = $pdo->prepare($queryLocais);
$statement->execute();
$retornalocais = $statement->fetchAll(PDO::FETCH_ASSOC);

$nomesLocais = array_column($retornalocais, 'nome_local');
$locais = $nomesLocais;
$retornaSearchs = '';

//Queyr retorna quantidade de usuários cadastrados
$queryQtdUser = "SELECT COUNT(*) as quantidade FROM usuarios";
$statement = $pdo->query($queryQtdUser);
$row = $statement->fetch(PDO::FETCH_ASSOC);
$quantidadeDeUsuarios = $row['quantidade'];
$msgsqlsearch = 'TOTAL DE USUÁRIOS ATIVOS: ';

//Query retorna quantidade de usuários ativos
$queryQtdUserAtivo = "SELECT COUNT(*) as quantidade_ativo FROM usuarios WHERE status_usuario = 1";
$statement = $pdo->query($queryQtdUserAtivo);
$rowativos = $statement->fetch(PDO::FETCH_ASSOC);
$quantidadeUsuariosAtivos = $rowativos['quantidade_ativo'];
//var_dump($retornaQtdUserAtivos);
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
    <link rel="stylesheet" href="../../assets/css/pesquisa.css">
    <link rel="shortcut icon" href="../../assets/images/fav.png">
    <title>Painel</title>
</head>

<body>
    <div id="erroMensagem" class="mensagem-erro">Nao foi possivel Realizar a Retirada de Chave.</div>
    <div id="sucMensagem" class="mensagem-suc">Retirada de Chave Realizada Com Sucesso!.</div>
    <div vw class="enabled">
        <div vw-access-button class="active"></div>
        <div vw-plugin-wrapper>
            <div class="vw-plugin-top-wrapper"></div>
        </div>
    </div>
    <div class="main">
        <main class="d-flex flex-nowrap side-bar">
            <div class="d-flex flex-column flex-shrink-0 p-3 text-bg-dark menu-left">
                <a href="https://projetopei.dev.br/app/views/painel.php" class="d-flex logo align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <span>PORTARIA DIGITAL</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="https://projetopei.dev.br/app/views/painel.php" class="nav-link text-white" aria-current="page">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-house-fill" viewBox="0 0 16 16">
                                <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L8 2.207l6.646 6.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5Z" />
                                <path d="m8 3.293 6 6V13.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5V9.293l6-6Z" />
                            </svg>
                            <use xlink:href="#hom"></use>
                            </i>
                            Inicio
                        </a>
                    </li>
                    <li>
                        <div class="li-usuarios">
                            <?php if ($tipoUsuarioLogado === 1) {
                                echo '<a href="#" class="nav-link text-white" data-bs-toggle="collapse" data-bs-target="#collapseusuarios" aria-expanded="false" aria-controls="collapseExample">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-person-circle" viewBox="0 0 16 16">
                                <path d="M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0z"/>
                                <path fill-rule="evenodd" d="M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8zm8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1z"/>
                            </svg>
                                Usuários <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-double-down svg-bottomchaves" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1.646 6.646a.5.5 0 0 1 .708 0L8 12.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                                    <path fill-rule="evenodd" d="M1.646 2.646a.5.5 0 0 1 .708 0L8 8.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                                </svg>
                            </a>';
                            } ?>
                        </div>
                        <div class="d-dowm-chaves">
                            <ul>
                                <div class="collapse" id="collapseusuarios">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="painel2" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#adduser">
                                    <i class="bi bi-person-add">
                                        <use xlink:href="#hom"></use>
                                    </i>
                                    Novo Usuário
                                </a>';
                                    } ?>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapseusuarios">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="usuarios_cadastrados.php" class="nav-link text-white r-chaves">
                                        <i class="bi bi-people-fill"></i>
                                        Usuários Registrados
                                    </a>';
                                    } ?>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#addocorrenciaa">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-journal-plus" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M8 5.5a.5.5 0 0 1 .5.5v1.5H10a.5.5 0 0 1 0 1H8.5V10a.5.5 0 0 1-1 0V8.5H6a.5.5 0 0 1 0-1h1.5V6a.5.5 0 0 1 .5-.5z" />
                                <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z" />
                                <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z" />
                            </svg>
                            Nova Ocorrência
                        </a>
                    </li>
                    <li>
                        <a href="#" class="nav-link text-white" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-car-front" viewBox="0 0 16 16">
                                <path d="M4 9a1 1 0 1 1-2 0 1 1 0 0 1 2 0Zm10 0a1 1 0 1 1-2 0 1 1 0 0 1 2 0ZM6 8a1 1 0 0 0 0 2h4a1 1 0 1 0 0-2H6ZM4.862 4.276 3.906 6.19a.51.51 0 0 0 .497.731c.91-.073 2.35-.17 3.597-.17 1.247 0 2.688.097 3.597.17a.51.51 0 0 0 .497-.731l-.956-1.913A.5.5 0 0 0 10.691 4H5.309a.5.5 0 0 0-.447.276Z" />
                                <path d="M2.52 3.515A2.5 2.5 0 0 1 4.82 2h6.362c1 0 1.904.596 2.298 1.515l.792 1.848c.075.175.21.319.38.404.5.25.855.715.965 1.262l.335 1.679c.033.161.049.325.049.49v.413c0 .814-.39 1.543-1 1.997V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.338c-1.292.048-2.745.088-4 .088s-2.708-.04-4-.088V13.5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1-.5-.5v-1.892c-.61-.454-1-1.183-1-1.997v-.413a2.5 2.5 0 0 1 .049-.49l.335-1.68c.11-.546.465-1.012.964-1.261a.807.807 0 0 0 .381-.404l.792-1.848ZM4.82 3a1.5 1.5 0 0 0-1.379.91l-.792 1.847a1.8 1.8 0 0 1-.853.904.807.807 0 0 0-.43.564L1.03 8.904a1.5 1.5 0 0 0-.03.294v.413c0 .796.62 1.448 1.408 1.484 1.555.07 3.786.155 5.592.155 1.806 0 4.037-.084 5.592-.155A1.479 1.479 0 0 0 15 9.611v-.413c0-.099-.01-.197-.03-.294l-.335-1.68a.807.807 0 0 0-.43-.563 1.807 1.807 0 0 1-.853-.904l-.792-1.848A1.5 1.5 0 0 0 11.18 3H4.82Z" />
                            </svg>
                            Registros de Veiculos <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-double-down svg-bottomchaves" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M1.646 6.646a.5.5 0 0 1 .708 0L8 12.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                                <path fill-rule="evenodd" d="M1.646 2.646a.5.5 0 0 1 .708 0L8 8.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                            </svg>
                        </a>
                        <div class="d-dowm-chaves">
                            <ul>
                                <div class="collapse" id="collapseExample">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#adicionaveiculo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z" />
                                        </svg>
                                        Adicionar Veiculo
                                    </a>';
                                    } ?>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapseExample">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#adicionamotorista">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z" />
                                        </svg>
                                        Adicionar Motorista
                                    </a>';
                                    } ?>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapseExample">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#motoristascad">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z" />
                                        </svg>
                                        Motoristas Cadastrados
                                    </a>';
                                    } ?>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapseExample">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#veiculoscad">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z" />
                                        </svg>
                                        Veiculos Cadastrados
                                    </a>';
                                    } ?>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapseExample">
                                    <a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#retiradaveiculo">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z" />
                                        </svg>
                                        Retirada de Veiculo
                                    </a>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapseExample">
                                    <a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#devolucaochave">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-arrow-return-right" viewBox="0 0 16 16">
                                            <path fill-rule="evenodd" d="M1.5 1.5A.5.5 0 0 0 1 2v4.8a2.5 2.5 0 0 0 2.5 2.5h9.793l-3.347 3.346a.5.5 0 0 0 .708.708l4.2-4.2a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 8.3H3.5A1.5 1.5 0 0 1 2 6.8V2a.5.5 0 0 0-.5-.5z" />
                                        </svg>
                                        Devolução
                                    </a>
                            </ul>
                        </div>

                    </li>
                    <li>
                        <a href="painel2" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#ultimasobservacoes">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-journal-check" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M10.854 6.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 1 1 .708-.708L7.5 8.793l2.646-2.647a.5.5 0 0 1 .708 0z" />
                                <path d="M3 0h10a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-1h1v1a1 1 0 0 0 1 1h10a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1H3a1 1 0 0 0-1 1v1H1V2a2 2 0 0 1 2-2z" />
                                <path d="M1 5v-.5a.5.5 0 0 1 1 0V5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0V8h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1zm0 3v-.5a.5.5 0 0 1 1 0v.5h.5a.5.5 0 0 1 0 1h-2a.5.5 0 0 1 0-1H1z" />
                            </svg>
                            <use xlink:href="#hom"></use>
                            </i>
                            Ultimas Observações
                        </a>
                    </li>
                    <li>
                        <?php if ($tipoUsuarioLogado === 1) {
                            echo '<a href="#" class="nav-link text-white" data-bs-toggle="collapse" data-bs-target="#collapselocais" aria-expanded="false" aria-controls="collapseExample">
                                <svg xmlns="http://www.w3.org/2000/svg" width="25" height="" fill="currentColor" class="bi bi-map" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M15.817.113A.5.5 0 0 1 16 .5v14a.5.5 0 0 1-.402.49l-5 1a.502.502 0 0 1-.196 0L5.5 15.01l-4.902.98A.5.5 0 0 1 0 15.5v-14a.5.5 0 0 1 .402-.49l5-1a.5.5 0 0 1 .196 0L10.5.99l4.902-.98a.5.5 0 0 1 .415.103zM10 1.91l-4-.8v12.98l4 .8V1.91zm1 12.98 4-.8V1.11l-4 .8v12.98zm-6-.8V1.11l-4 .8v12.98l4-.8z"/>
                                </svg>
                                Locais <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-chevron-double-down svg-bottomchaves" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M1.646 6.646a.5.5 0 0 1 .708 0L8 12.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                                    <path fill-rule="evenodd" d="M1.646 2.646a.5.5 0 0 1 .708 0L8 8.293l5.646-5.647a.5.5 0 0 1 .708.708l-6 6a.5.5 0 0 1-.708 0l-6-6a.5.5 0 0 1 0-.708z" />
                                </svg>
                            </a>';
                        } ?>
                        <div class="d-dowm-chaves">
                            <ul>
                                <div class="collapse" id="collapselocais">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#adicionalocal">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        Adicionar Novo Local
                                        </a>';
                                    } ?>
                            </ul>
                            <ul>
                                <div class="collapse" id="collapselocais">
                                    <?php if ($tipoUsuarioLogado === 1) {
                                        echo '<a href="#" class="nav-link text-white r-chaves" data-bs-toggle="modal" data-bs-target="#locaisregistrados">
                                        <i class="bi bi-geo-alt-fill"></i>
                                        Locais Registrados
                                        </a>';
                                    } ?>
                            </ul>
                        </div>
                    </li>
                    <li>
                        <?php if ($tipoUsuarioLogado === 1) {
                            echo '<a href="#" class="nav-link text-white" data-bs-toggle="modal" data-bs-target="#relatorioss">
                            <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-file-earmark-pdf" viewBox="0 0 16 16">
                            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
                            <path d="M4.603 14.087a.81.81 0 0 1-.438-.42c-.195-.388-.13-.776.08-1.102.198-.307.526-.568.897-.787a7.68 7.68 0 0 1 1.482-.645 19.697 19.697 0 0 0 1.062-2.227 7.269 7.269 0 0 1-.43-1.295c-.086-.4-.119-.796-.046-1.136.075-.354.274-.672.65-.823.192-.077.4-.12.602-.077a.7.7 0 0 1 .477.365c.088.164.12.356.127.538.007.188-.012.396-.047.614-.084.51-.27 1.134-.52 1.794a10.954 10.954 0 0 0 .98 1.686 5.753 5.753 0 0 1 1.334.05c.364.066.734.195.96.465.12.144.193.32.2.518.007.192-.047.382-.138.563a1.04 1.04 0 0 1-.354.416.856.856 0 0 1-.51.138c-.331-.014-.654-.196-.933-.417a5.712 5.712 0 0 1-.911-.95 11.651 11.651 0 0 0-1.997.406 11.307 11.307 0 0 1-1.02 1.51c-.292.35-.609.656-.927.787a.793.793 0 0 1-.58.029zm1.379-1.901c-.166.076-.32.156-.459.238-.328.194-.541.383-.647.547-.094.145-.096.25-.04.361.01.022.02.036.026.044a.266.266 0 0 0 .035-.012c.137-.056.355-.235.635-.572a8.18 8.18 0 0 0 .45-.606zm1.64-1.33a12.71 12.71 0 0 1 1.01-.193 11.744 11.744 0 0 1-.51-.858 20.801 20.801 0 0 1-.5 1.05zm2.446.45c.15.163.296.3.435.41.24.19.407.253.498.256a.107.107 0 0 0 .07-.015.307.307 0 0 0 .094-.125.436.436 0 0 0 .059-.2.095.095 0 0 0-.026-.063c-.052-.062-.2-.152-.518-.209a3.876 3.876 0 0 0-.612-.053zM8.078 7.8a6.7 6.7 0 0 0 .2-.828c.031-.188.043-.343.038-.465a.613.613 0 0 0-.032-.198.517.517 0 0 0-.145.04c-.087.035-.158.106-.196.283-.04.192-.03.469.046.822.024.111.054.227.09.346z"/>
                            </svg>
                            Relatórios
                        </a>';
                        } ?>
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
            <div class="geral">
                <div class="text-center">
                    <h1></h1>
                </div>
                <div>
                    <div class="table-info">
                        <div class="box-pesquisa">
                            <div class="titulo-box-pesquisa">
                                <h1>Usuários Cadastrados</h1>
                            </div>
                        </div>
                        <div class="tabela-principal">
                            <div>
                                <table class="table col-xs-7 table-bordered table-striped table-condensed table-fixed text-center">
                                    <thead>
                                        <tr>
                                            <th scope="col">NOME COMPLETO</th>
                                            <th scope="col">USUÁRIO</th>
                                            <th scope="col">PERFIL</th>
                                            <th scope="col">STATUS</th>
                                            <th scope="col">EDITAR</th>
                                            <th scope="col">SENHAS <svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-key-fill" viewBox="0 0 16 16">
                                                    <path d="M3.5 11.5a3.5 3.5 0 1 1 3.163-5H14L15.5 8 14 9.5l-1-1-1 1-1-1-1 1-1-1-1 1H6.663a3.5 3.5 0 0 1-3.163 2zM2.5 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2z" />
                                                </svg></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($usuarios as $usuario) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                            <tr>
                                                <td><?php echo $usuario['nome']; ?></td>
                                                <td><?php echo $usuario['usuario']; ?></td>
                                                <td><?php echo ($usuario['tipo_usuario'] == 1 ? 'Administrador' : 'Usuário Normal') ?></td>
                                                <td><?php echo ($usuario['status_usuario'] == 1 ? 'Ativo' : 'Desativado') ?></td>
                                                <td>
                                                    <?php if ($usuario['usuario'] !== 'admin') : ?>
                                                        <form class="d-flex " action="processaHabilitacaoUser.php" method="POST">
                                                            <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                                            <select class="form-select" name="novo_status" id="novo_status">
                                                                <option value="" selected disabled>Selecione</option>
                                                                <?php if ($usuario['status_usuario'] == 1) : ?>
                                                                    <!-- Usuário está ativo, exibir opção de desativar -->
                                                                    <option value="0">Desativar</option>
                                                                <?php else : ?>
                                                                    <!-- Usuário está desativado, exibir opção de ativar -->
                                                                    <option value="1">Ativar</option>
                                                                <?php endif; ?>
                                                            </select>
                                                            <button type="submit" name="alterastatususer" id="alterastatususer" class="btn btn-primary btn-alterastatus">Salvar</button>
                                                        </form>
                                                    <?php else : ?>
                                                        <!-- Exibir uma mensagem ou outra indicação aqui para o usuário 'admin' -->
                                                        <p>Administrador Geral</p>
                                                    <?php endif; ?>
                                                </td>
                                                <td><button type="button" class="btn btn-primary editar-senha-button" data-bs-toggle="modal" data-bs-target="#modalEditarSenha<?php echo $usuario['id']; ?>">
                                                        Alterar Senha
                                                    </button></td>
                                            </tr>
                                            <!-- Modal de Edição de Senha para cada usuário -->
                                            <div class="modal fade" id="modalEditarSenha<?php echo $usuario['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="modalEditarSenhaLabel<?php echo $usuario['id']; ?>">
                                                <div class="modal-dialog" role="document">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h5 class="modal-title" id="modalEditarSenhaLabel<?php echo $usuario['id']; ?>">Alterar Senha de <b><?php echo $usuario['usuario']; ?></b></h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <!-- Formulário de edição de senha -->
                                                            <form action="processaAlteraSenha.php" method="POST">
                                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                                                <div class="form-group">
                                                                    <label for="nova_senha">Nova Senha</label>
                                                                    <input type="password" class="form-control" id="nova_senha" name="nova_senha" required>
                                                                </div>
                                                                <button type="submit" name="alterasenha" class="btn btn-primary btn-salva-senha">Salvar Senha</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="table-footer">
                            <div class="totalfooter">
                                <h1>TOTAL DE USUÁRIOS: <?php echo $quantidadeDeUsuarios ?></h1>
                            </div>
                            <div class="paginacao">
                                <div class="pagination text-white">
                                    <h4><?php echo $msgsqlsearch . $quantidadeUsuariosAtivos ?></h4>
                                </div>
                            </div>
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
                                                            <label for="local" class="form-label"><b>Escolha um local:</b></label>
                                                            <input class="form-control" list="localOptions" id="local" name="local" placeholder="Digite para pesquisar..." required>
                                                            <datalist id="localOptions">
                                                                <?php foreach ($locais as $local) : ?>
                                                                    <option value="<?php echo $local; ?>">
                                                                    <?php endforeach; ?>
                                                            </datalist>
                                                            <span id="localValidationMessage"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="descricao" class="form-label"><b>Relatório Da Ocorrência</b></label>
                                                    <textarea class="form-control" id="descricao" name="descricao" rows="3" maxlength="1000" placeholder="Relate a Ocorrência" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <button onclick="cadastraOcorrencia()" type="submit" name="cadastraOcorrencia" id="cadastraOcorrencia" class="btn btn-primary">Cadastrar</button>
                                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
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
                        <form method="POST" action="processaUsuario.php">
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
                            <div class="modal-footer">
                                <button type="submit" name="adduser" id="adduser" class="btn btn-primary">Cadastrar</button>
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
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
                <div class="modal-body text-center">
                    <table class="table-usuarios table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th scope="col">NOME COMPLETO</th>
                                <th scope="col">USUARIO</th>
                                <th scope="col">PERFIL</th>
                                <th scope="col">STATUS</th>
                                <th scope="col">ACAO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $usuario) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                <tr>
                                    <td><?php echo $usuario['nome']; ?></td>
                                    <td><?php echo $usuario['usuario']; ?></td>
                                    <td><?php echo ($usuario['tipo_usuario'] == 1 ? 'Administrador' : 'Usuário Normal') ?></td>
                                    <td><?php echo ($usuario['status_usuario'] == 1 ? 'Ativo' : 'Desativado') ?></td>
                                    <td>
                                        <?php if ($usuario['usuario'] !== 'admin') : ?>
                                            <form class="d-flex " action="processaHabilitacaoUser.php" method="POST">
                                                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id']; ?>">
                                                <select class="form-select" name="novo_status" id="novo_status">
                                                    <option value="" selected disabled>Selecione</option>
                                                    <?php if ($usuario['status_usuario'] == 1) : ?>
                                                        <!-- Usuário está ativo, exibir opção de desativar -->
                                                        <option value="0">Desativar</option>
                                                    <?php else : ?>
                                                        <!-- Usuário está desativado, exibir opção de ativar -->
                                                        <option value="1">Ativar</option>
                                                    <?php endif; ?>
                                                </select>
                                                <button type="submit" name="alterastatususer" id="alterastatususer" class="btn btn-primary btn-alterastatus">Salvar</button>
                                            </form>
                                        <?php else : ?>
                                            <!-- Exibir uma mensagem ou outra indicação aqui para o usuário 'admin' -->
                                            <p>Administrador Geral</p>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                    </div>
                    </form>
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

    <!-- Modal RETIRADA DE VEICULO -->
    <div class="modal fade" id="retiradaveiculo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-x">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Retirada de Veiculo</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL RETIRADA DE VEICULO-->
                <div class="modal-body">
                    <form method="POST" action="processaRetiradaVeiculo.php">
                        <div class="mb-3">
                            <label for="usuarioResponsavel" class="form-label"><b>Responsável pelo Veiculo:</b></label>
                            <select class="form-select" id="usuarioResponsavel" name="usuarioResponsavel" required>
                                <option value="" disabled selected>Selecione o Responsavel</option>
                                <?php foreach ($motoristas as $motorista) : ?>
                                    <?php if ($motorista['status_motorista'] != 0) : ?>
                                        <option value="<?php echo $motorista['id']; ?>"><?php echo $motorista['nome']; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                            <span id="motoristaValidationMessage"></span>
                        </div>
                        <div class="mb-3">
                            <label for="tipoVeiculo" class="form-label"><b>Selecione o Veículo:</b></label>
                            <select class="form-select" id="nomeVeiculo" name="nomeVeiculo" required>
                                <option value="" disabled selected>Selecione o tipo de veículo</option>
                                <?php foreach ($veiculos as $veiculo) : ?>
                                    <?php if ($veiculo['status_veiculo'] != 0) : ?>
                                        <option value="<?php echo $veiculo['id']; ?>"><?php echo $veiculo['nome']; ?></option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="dataRetirada" class="form-label"><b>Data e Hora da Retirada:</b></label>
                            <input type="datetime-local" class="form-control" id="dataRetirada" name="dataRetirada" required>
                        </div>
                        <div class="mb-3">
                            <label for="destino" class="form-label"><b>Informe o Destino do Veículo:</b></label>
                            <textarea class="form-control" id="destino" name="destino" rows="4" placeholder="Informe o destino do veículo" required></textarea>
                        </div>
                        <input type="hidden" name="statusRetirada" value="ativa">
                        <div class="modal-footer">
                            <button type="submit" name="cadretiradaveiculo" id="cadretiradaveiculo" class="btn btn-primary">Cadastrar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ADICIONA VEICULO -->
    <div class="modal fade" id="adicionaveiculo" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-x">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Adicionar Novo Veiculo</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL ADICIONA VEICULO-->
                <div class="modal-body">
                    <form method="POST" action="processaVeiculo.php">
                        <div class="mb-3">
                            <label for="tipo_veiculo" class="form-label"><b>Tipo de Veículo:</b></label>
                            <select class="form-select custom-width-motorista" id="tipo_veiculo" name="tipo_veiculo" required>
                                <option value="" disabled selected>Selecione</option>
                                <option value="Carro">Carro</option>
                                <option value="Moto">Moto</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="nome" class="form-label"><b>Nome do Veículo:</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="nome" name="nome" placeholder="Insira o nome do veículo" required>
                        </div>

                        <div class="mb-3">
                            <label for="placa" class="form-label"><b>Placa:</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="placa" name="placa" placeholder="Insira a placa" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="cadveiculo" id="cadveiculo" class="btn btn-primary">Cadastrar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal VEICULOS CADASTRADOS -->
    <div class="modal fade" id="veiculoscad" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-x">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Veiculos Cadastrados</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL VEICULO CADASTRADOS -->
                <div class="modal-body text-center">
                    <table class="table table-bordered table-striped table-condensed table-fixed text-center">
                        <thead>
                            <tr>
                                <th scope="col">VEÍCULO</th>
                                <th scope="col">NOME</th>
                                <th scope="col">PLACA</th>
                                <th scope="col">STATUS</th>
                                <th scope="col">AÇÃO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($veiculos as $veiculo) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                <tr>
                                    <td><?php echo $veiculo['tipo_veiculo']; ?></td>
                                    <td><?php echo $veiculo['nome']; ?></td>
                                    <td><?php echo $veiculo['placa']; ?></td>
                                    <td>
                                        <?php
                                        $status = $veiculo['status_veiculo'];
                                        if ($status == 1) {
                                            echo 'Ativo';
                                        } else {
                                            echo 'Inativo';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $idVeiculo = $veiculo['id'];
                                        $botaoLabel = ($status == 1) ? 'Desativar' : 'Ativar';
                                        $botaoClass = ($status == 1) ? 'btn-danger desativar-veiculo' : 'btn-primary ativar-veiculo';
                                        ?>
                                        <button class="btn <?php echo $botaoClass; ?>" data-id="<?php echo $idVeiculo; ?>">
                                            <?php echo $botaoLabel; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary"><a href="#" class="r-chaves text-white" data-bs-toggle="modal" data-bs-target="#adicionaveiculo">
                                Cadastrar Veiculo
                            </a></button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal MOTORISTAS CADASTRADOS -->
    <div class="modal fade" id="motoristascad" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-x">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Motoristas Cadastrados</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL MOTORISTAS CADASTRADOS -->
                <div class="modal-body text-center">
                    <table class="table table-bordered table-striped table-condensed table-fixed text-center">
                        <thead>
                            <tr>
                                <th scope="col">NOME</th>
                                <th scope="col">SETOR</th>
                                <th scope="col">STATUS</th>
                                <th scope="col">AÇÃO</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($motoristas as $motorista) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                <tr>
                                    <td><?php echo $motorista['nome']; ?></td>
                                    <td><?php echo $motorista['setor']; ?></td>
                                    <td>
                                        <?php
                                        $status = $motorista['status_motorista'];
                                        if ($status == 1) {
                                            echo 'Ativo';
                                        } else {
                                            echo 'Inativo';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $idMotorista = $motorista['id'];
                                        $botaoLabel = ($status == 1) ? 'Desativar' : 'Ativar';
                                        $botaoClass = ($status == 1) ? 'btn-danger desativar-motorista' : 'btn-success ativar-motorista';
                                        ?>
                                        <button class="btn <?php echo $botaoClass; ?>" data-id="<?php echo $idMotorista; ?>">
                                            <?php echo $botaoLabel; ?>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-primary"><a href="#" class="r-chaves text-white" data-bs-toggle="modal" data-bs-target="#adicionamotorista">
                                Cadastrar Motorista
                            </a></button>
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ADICIONA MOTORISTA -->
    <div class="modal fade" id="adicionamotorista" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-x">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Adicionar Novo Motorista</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL ADICIONA MOTORISTA-->
                <div class="modal-body">
                    <form method="POST" action="processaMotorista.php">
                        <div class="mb-3">
                            <label for="motorista" class="form-label"><b>Nome do Motorista:</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="motorista" name="motorista" placeholder="Insira o nome do motorista" required>
                        </div>
                        <div class="mb-3">
                            <label for="setor" class="form-label"><b>Setor do Motorista:</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="setor" name="setor" placeholder="Insira o setor do motorista" required>
                        </div>

                        <div class="mb-3">
                            <label for="cpf" class="form-label"><b>CPF do Motorista:</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="cpf" name="cpf" placeholder="Insira o CPF do motorista" required>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" name="cadmotorista" id="cadmotorista" class="btn btn-primary">Cadastrar</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal DEVOLUCAO -->
    <div class="modal fade" id="devolucaochave" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Devolução de Chaves</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL DEVOLUCAO-->
                <div class="modal-body ">
                    <div class="container">
                        <form action="processaDevolucao.php" method="post">
                            <table class="table table-bordered table-striped table-condensed table-fixed text-center">
                                <thead>
                                    <tr>
                                        <th>NOME DO MOTORISTA</th>
                                        <th>NOME DO VEÍCULO</th>
                                        <th>DESTINO</th>
                                        <th>DATA DE RETIRADA</th>
                                        <th>DATA DE DEVOLUÇÃO</th>
                                        <th>REGISTRAR DEVOLUÇÃO</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($retiradaVeiculos as $retiradaVeiculo) : ?>
                                        <tr>
                                            <td><?php echo $retiradaVeiculo['nome_motorista']; ?></td>
                                            <td><?php echo $retiradaVeiculo['nome_veiculo']; ?></td>
                                            <td><?php echo $retiradaVeiculo['destino']; ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($retiradaVeiculo['data_retirada'])); ?></td>
                                            <td><?php
                                                $dataDevolucao = date('d/m/Y H:i', strtotime($retiradaVeiculo['data_devolucao']));
                                                echo ($dataDevolucao == '01/01/1970 01:00') ? 'Sem Data Devolução' : $dataDevolucao;
                                                ?></td>
                                            <td>
                                                <?php if (empty($retiradaVeiculo['data_devolucao'])) : ?>
                                                    <form action="processaDevolucao.php" method="post">
                                                        <div class="btn-registra-devolucao">
                                                            <input type="hidden" name="idRetiradaVeiculo" value="<?php echo $retiradaVeiculo['id']; ?>">
                                                            <input type="datetime-local" class="form-control" name="dataDevolucao" required>

                                                        </div>
                                                        <input type="hidden" name="statusDevolucao" value="devolvido">
                                                    </form>
                                                <?php else : ?>
                                                    <div class="btn-devolucao-realizada">
                                                        <p><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-check2-square" viewBox="0 0 16 16">
                                                                <path d="M3 14.5A1.5 1.5 0 0 1 1.5 13V3A1.5 1.5 0 0 1 3 1.5h8a.5.5 0 0 1 0 1H3a.5.5 0 0 0-.5.5v10a.5.5 0 0 0 .5.5h10a.5.5 0 0 0 .5-.5V8a.5.5 0 0 1 1 0v5a1.5 1.5 0 0 1-1.5 1.5H3z" />
                                                                <path d="m8.354 10.354 7-7a.5.5 0 0 0-.708-.708L8 9.293 5.354 6.646a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0z" />
                                                            </svg>Devolução Realizada</p>
                                                    </div>
                                                <?php endif; ?>
                                            </td>

                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <div class="info-footer-tabela">
                                        <p><svg xmlns="http://www.w3.org/2000/svg" width="25" height="25" fill="currentColor" class="bi bi-exclamation-circle" viewBox="0 0 16 16">
                                                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
                                                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z" />
                                            </svg> Atenção, ao registrar data de devolução, uma vez que registrada não será possível alterar.</p>
                                    </div>
                                </tfoot>
                            </table>

                        </form>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" name="devolucao" id="devolucao">Registrar Devolução</button>
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="ultimasobservacoes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Ultimas Observações Registradas</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL ULTIMAS OBSERVACOES-->
                <div class="modal-body text-center">
                    <table class="table-usuarios table table-bordered table-hover table-striped table-condensed table-fixed text-center">
                        <thead>
                            <tr>
                                <th scope="col">OBSERVAÇÃO</th>
                                <th scope="col">TÍTULO</th>
                                <th scope="col">DATA REGISTRO</th>
                                <th scope="col">USUÁRIO REGISTROU</th>
                                <th scope="col">ID OCORRÊNCIA</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($totalObservacoes as $observacao) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                <tr>
                                    <td><?php echo substr($observacao['observacao'], 0, 20); ?></td>
                                    <td><?php echo $observacao['titulo_ocorrencia']; ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($observacao['data_registro'])); ?></td>
                                    <td><?php echo $observacao['nome_usuario']; ?></td>
                                    <td><?php echo $observacao['id_ocorrencia']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal ADICIONA NOVO LOCAL -->
    <div class="modal fade" id="adicionalocal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-x">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Adicionar Novo Local Para Ocorrências</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL ADICIONA NOVO LOCAL-->
                <div class="modal-body">
                    <form method="POST" action="processaLocal.php">
                        <div class="mb-3">
                            <label for="local" class="form-label"><b>Nome do Local</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="local" name="local" placeholder="Informe o novo local" required>
                        </div>

                        <div class="mb-3">
                            <label for="bloco" class="form-label"><b>Bloco</b></label>
                            <input type="text" class="form-control custom-width-motorista" id="bloco" name="bloco" placeholder="EX: Bloco 1" required>
                        </div>
                        <hr>
                        <div class="mb-3">
                            <button type="submit" id="cadlocal" name="cadlocal" class="btn btn-primary">Cadastrar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal LOCAIS REGISTRADOS -->
    <div class="modal fade" id="locaisregistrados" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5" id="staticBackdropLabel"><b>Locais Registrados</b></h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!-- CORPO DO MODAL LOCAIS REGISTRADOS-->
                <div class="modal-body text-center">
                    <div class="search-locais">
                        <input class="form-control" type="text" id="barraDePesquisa" placeholder="Pesquisar...">
                    </div>
                    <table class="table-usuarios table table-bordered table-striped table-condensed table-fixed text-center">
                        <thead>
                            <tr>
                                <th scope="col">Local</th>
                                <th scope="col">Bloco</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($retornalocais as $retornalocal) : ?> <!-- Loop para que enquanto exista registro ele mostre na tela -->
                                <tr>
                                    <td><?php echo $retornalocal['nome_local']; ?></td>
                                    <td><?php echo $retornalocal['bloco']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://vlibras.gov.br/app/vlibras-plugin.js"></script>
    <script>
        new window.VLibras.Widget('https://vlibras.gov.br/app');
    </script>
    <script src="../../assets/scripts/scripts.js"></script>
    <script src="../../assets/scripts/jQuery.min.js"></script>
    <script>
        // Chamar as funções quando a página estiver carregada
        $(document).ready(function() {
            verificaNomeUsuario();
            verificaLocal();
        });
    </script>
    <script>
        var search = document.getElementById('pesquisar');

        search.addEventListener("keydown", function(event) {
            if (event.key === "Enter") {
                searchData();
            }
        });

        function searchData() {
            window.location = 'painel.php?search=' + search.value;

        }
    </script>
    <script>
        // Obtém a data e hora atual
        const dataHoraAtual = new Date();
        // Formata a data e hora no formato esperado (AAAA-MM-DDTHH:MM)
        const formatoDataHora = `${dataHoraAtual.getFullYear()}-${(dataHoraAtual.getMonth() + 1).toString().padStart(2, '0')}-${dataHoraAtual.getDate().toString().padStart(2, '0')}T${dataHoraAtual.getHours().toString().padStart(2, '0')}:${dataHoraAtual.getMinutes().toString().padStart(2, '0')}`;
        // Define o valor do input como a data e hora formatada
        document.getElementById("dataRetirada").value = formatoDataHora;
    </script>
    <script>
        if (window.location.href.indexOf('?erro=1') !== -1) {
            // A URL contém "?erro=1", mostre a mensagem de erro
            document.getElementById('erroMensagem').style.display = 'block';

            // Adicione um atraso de 5 segundos (5000 milissegundos) para ocultar a mensagem
            setTimeout(function() {
                document.getElementById('erroMensagem').style.display = 'none';

                // Remova "?erro=1" da URL usando pushState
                const newURL = window.location.href.replace('?erro=1', '');
                window.history.pushState({}, document.title, newURL);
            }, 6000);
        } else if (window.location.href.indexOf('?sucesso=1') !== -1) {
            // A URL contém "?sucesso=1", mostre a mensagem de sucesso
            document.getElementById('sucMensagem').style.display = 'block';

            // Adicione um atraso de 5 segundos (5000 milissegundos) para ocultar a mensagem
            setTimeout(function() {
                document.getElementById('sucMensagem').style.display = 'none';

                // Remova "?sucesso=1" da URL usando pushState
                const newURL = window.location.href.replace('?sucesso=1', '');
                window.history.pushState({}, document.title, newURL);
            }, 5000);
        }
    </script>
    <script>
        function cadastraOcorrencia() {
            // Obtém o botão pelo seu ID
            var botao = document.getElementById("cadastraOcorrencia");

            // Desativa o botão
            botao.disabled = true;

            // Define um atraso de 2 segundos (2000 milissegundos) para reativar o botão
            setTimeout(function() {
                botao.disabled = false;
            }, 5000);
        }
    </script>
    <script>
        $(document).ready(function() {
            // Quando o usuário digita na barra de pesquisa
            $('#barraDePesquisa').keyup(function() {
                // Obter o valor digitado na barra de pesquisa
                var termoDePesquisa = $(this).val().toLowerCase();

                // Percorrer cada linha da tabela e ocultar/mostrar com base na pesquisa
                $('.table-usuarios tbody tr').each(function() {
                    var linha = $(this);
                    var nomeLocal = linha.find('td:eq(0)').text().toLowerCase();
                    var bloco = linha.find('td:eq(1)').text().toLowerCase();

                    if (nomeLocal.indexOf(termoDePesquisa) !== -1 || bloco.indexOf(termoDePesquisa) !== -1) {
                        linha.show();
                    } else {
                        linha.hide();
                    }
                });
            });
        });
    </script>

    <script>
        $(document).ready(function() {
            $('.ativar-motorista, .desativar-motorista').click(function() {
                var idMotorista = $(this).data('id');
                var novoStatus = $(this).hasClass('ativar-motorista') ? 1 : 0; // Verifique a classe do botão

                // Armazene a referência ao botão atual para uso posterior
                var botao = $(this);

                // Envie uma solicitação AJAX para o servidor para alterar o status do motorista
                $.ajax({
                    url: 'altera_status_motorista.php', // Substitua pelo URL correto do seu script de servidor
                    method: 'POST',
                    data: {
                        id_motorista: idMotorista,
                        novo_status: novoStatus
                    },
                    success: function(response) {
                        // Atualize a tabela ou faça qualquer outra coisa necessária após a alteração de status
                        if (novoStatus === 1) {
                            alert('Motorista ativado com sucesso.');
                        } else {
                            alert('Motorista desativado com sucesso.');
                        }

                        // Recarregue a página
                        location.reload();
                    },
                    error: function() {
                        alert('Ocorreu um erro ao alterar o status do motorista.');
                    }
                });
            });
        });
    </script>
    <script>
        $(document).ready(function() {
            $('.ativar-veiculo, .desativar-veiculo').click(function() {
                var idVeiculo = $(this).data('id');
                var novoStatus = $(this).hasClass('ativar-veiculo') ? 1 : 0; // Verifique a classe do botão

                // Armazene a referência ao botão atual para uso posterior
                var botao = $(this);

                // Envie uma solicitação AJAX para o servidor para alterar o status do motorista
                $.ajax({
                    url: 'altera_status_veiculo.php', // Substitua pelo URL correto do seu script de servidor
                    method: 'POST',
                    data: {
                        id_veiculo: idVeiculo,
                        novo_status: novoStatus
                    },
                    success: function(response) {
                        // Atualize a tabela ou faça qualquer outra coisa necessária após a alteração de status
                        if (novoStatus === 1) {
                            alert('Veículo ativado com sucesso.');
                        } else {
                            alert('Veículo desativado com sucesso.');
                        }

                        // Recarregue a página
                        location.reload();
                    },
                    error: function() {
                        alert('Ocorreu um erro ao alterar o status do veículo.');
                    }
                });
            });
        });
    </script>
</body>

</html>