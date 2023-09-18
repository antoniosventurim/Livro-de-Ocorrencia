<?php
session_start();

$mensagem = "";

// Limpar a mensagem de erro ao carregar a página
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    unset($_SESSION['mensagem']);
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
    <link rel="shortcut icon" href="./assets/images/fav.png">
    <title>Livro de Ocorrências</title>
    <link rel="stylesheet" href="./assets/css/styles.css">
</head>

<body>
    <div class="main-login">
        <div class="left-login">
        </div>
        <form action="./app/views/processaAutenticacao.php" method="post">
            <div class="right-login">
                <div class="card-login">
                    <h2>Entre com seu usuário e senha para continuar.</h2>
                    <div class="text-fild">
                        <label for="usuario"><ion-icon name="person"></ion-icon>Usuário</label>
                        <input type="text" name="usuario" placeholder="Usuário" required>
                    </div>
                    <div class="text-fild">
                        <label for="senha"><ion-icon name="lock-open"></ion-icon>Senha</label>
                        <input type="password" name="senha" placeholder="Senha" required>
                    </div>
                    <?php if ($mensagem !== "") { ?>
                        <div class="msg-erro">
                            <p><?php echo $mensagem; ?></p>
                        </div>
                    <?php } ?>
                    <div>
                        <input type="checkbox" name="savelogin" id="">
                        <label for="savelogin">Mantenha-me conectado</label>
                    </div>
                    <button type="submit" class="btn-login">LOGIN</button>
                    <tfoot>&copy;PEI II</tfoot>
                </div>
            </div>
        </form>
    </div>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>