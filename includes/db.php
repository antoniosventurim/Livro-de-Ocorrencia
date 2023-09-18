<?php
// Configurações do banco de dados
$DB_HOST = 'aws.connect.psdb.cloud';
$DB_USERNAME = 'blsb041bnriu5ef10tpm';
$DB_PASSWORD = 'pscale_pw_ySLoxOtaejTRmtoAvg0W42cP32AKuon1QHvsKdDqGJn';
$DB_NAME = 'portaria';


// Tentativa de conexão
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;", $DB_USERNAME, $DB_PASSWORD);

    // Configurar o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Logado Com sucesso";
    // Outras configurações opcionais
    // $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
