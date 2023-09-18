<?php
// Configurações do banco de dados
$DB_HOST = 'aws.connect.psdb.cloud';
$DB_USERNAME = 'wxqnfeza6gq60xsaagm0';
$DB_PASSWORD = 'pscale_pw_OA5QyGcWn2KChfrwUMlm148NzJkX1Q7xVbnXKok569k';
$DB_NAME = 'loRzF5NGDPLAUdbpaZfA';


// Tentativa de conexão
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;", $username, $password);

    // Configurar o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Logado Com sucesso";
    // Outras configurações opcionais
    // $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
