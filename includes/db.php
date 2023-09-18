<?php
// Configurações do banco de dados
$host = 'containers-us-west-53.railway.app';
$dbname = 'railway';
$username = 'root';
$password = 'loRzF5NGDPLAUdbpaZfA';
$port = 8066;

// Tentativa de conexão
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;port=$port", $username, $password);

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
