<?php
// Arquivo verificar_local.php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $local = $_POST['local'];

    // Consulta SQL para verificar se o nome do local já existe
    $query = "SELECT COUNT(*) AS count FROM locais WHERE nome_local = :local";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':local', $local);
    $statement->execute();

    // Obtenha o resultado da consulta
    $resultado = $statement->fetch(PDO::FETCH_ASSOC);

    if ($resultado['count'] > 0) {
        // O nome do local existe, retorne uma resposta JSON indicando que é válido
        echo json_encode(['valid' => true]);
    } else {
        // O nome do local não existe, retorne uma resposta JSON indicando que não é válido
        echo json_encode(['valid' => false]);
    }
}
