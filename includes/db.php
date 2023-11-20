<?php
// Configurações do banco de dados
$DB_HOST = '';
$DB_USERNAME = '';
$DB_PASSWORD = '';
$DB_NAME = '';


// Tentativa de conexão
try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;", $DB_USERNAME, $DB_PASSWORD);
    // Configurar o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Logado Com sucesso";
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
