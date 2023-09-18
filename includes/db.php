<?php
// Configurações do banco de dados
$host = 'localhost';
$dbname = 'portaria5';
$username = 'root';
$password = '';

// Tentativa de conexão
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);

    // Configurar o PDO para lançar exceções em caso de erros
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    //echo "Logado Com sucesso";
    // Outras configurações opcionais
    // $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    // $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
?>
