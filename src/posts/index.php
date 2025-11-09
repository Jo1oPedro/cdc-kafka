<?php

use Cdc\Kafka\DatabaseConnection;

require_once __DIR__ . "/../../vendor/autoload.php";

$dotEnv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotEnv->load();

$pdo = DatabaseConnection::connect();

try {
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS posts (
            id SERIAL PRIMARY KEY,
            nome VARCHAR(255) NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS comments (
            id SERIAL PRIMARY KEY,
            comment TEXT NOT NULL,
            post_id INTEGER NOT NULL,
            CONSTRAINT fk_post FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
        );
    ");

    echo 'Tabelas criadas/verificadas com sucesso ✅' . PHP_EOL;

    $stmt = $pdo->prepare("INSERT INTO posts (nome) VALUES (:nome)");
    $stmt->execute([':nome' => 'Meu primeiro post']);

    $lastId = $pdo->lastInsertId();
    echo "Post inserido com sucesso! ID: {$lastId}" . PHP_EOL;

    // Mostrar todos os posts cadastrados
    $posts = $pdo->query("SELECT * FROM posts")->fetchAll();
    echo "Posts existentes:" . PHP_EOL;
    foreach ($posts as $post) {
        echo "- [{$post['id']}] {$post['nome']}" . PHP_EOL;
    }

    // Mostrar todos os comentarios cadastrados
    $comments = $pdo->query("SELECT * FROM comments")->fetchAll();
    echo "Comentarios existentes:" . PHP_EOL;
    foreach ($comments as $comment) {
        echo "- [{$comment['id']}] {$comment['comment']}" . PHP_EOL;
    }
} catch (PDOException $e) {
    echo 'Erro ao criar tabelas: ' . $e->getMessage() . PHP_EOL;
}

