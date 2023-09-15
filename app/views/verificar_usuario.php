<?php
// Arquivo verificar_usuario.php
session_start();
require_once(__DIR__ . '/../../includes/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'];

    // Consulta SQL para verificar se o nome de usuário já existe
    $query = "SELECT COUNT(*) AS count FROM usuarios WHERE usuario = :usuario";
    $statement = $pdo->prepare($query);
    $statement->bindParam(':usuario', $usuario);
    $statement->execute();

    // Obtenha o resultado da consulta
    $resultado = $statement->fetch(PDO::FETCH_ASSOC);

    if ($resultado['count'] > 0) {
        // O nome de usuário já existe, retorne uma resposta JSON indicando que não é válido
        echo json_encode(['valid' => false]);
    } else {
        // O nome de usuário está disponível, retorne uma resposta JSON indicando que é válido
        echo json_encode(['valid' => true]);
    }
}
