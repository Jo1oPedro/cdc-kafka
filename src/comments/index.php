<?php

use Cdc\Kafka\DatabaseConnection;

require_once __DIR__ . "/../../vendor/autoload.php";

$dotEnv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotEnv->load();

$pdo = DatabaseConnection::connect();

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comments (
            id SERIAL PRIMARY KEY,
            comment TEXT NOT NULL,
            post_id INTEGER NOT NULL
        );
    ");

    echo 'Tabela criada/verificada com sucesso ✅' . PHP_EOL;

    $stmt = $pdo->prepare("INSERT INTO comments (comment, post_id) VALUES (:comment, :post_id)");
    $stmt->execute([
        ':comment' => 'Este é um comentário de teste no MySQL!',
        ':post_id' => 1,
    ]);

    echo "Comentário inserido com sucesso para o post #1 ✅" . PHP_EOL;
} catch (PDOException $e) {
    echo 'Erro ao criar tabela: ' . $e->getMessage() . PHP_EOL;
}