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


$conf = new RdKafka\Conf();

// ID único do consumer group
$conf->set('group.id', 'php-consumer-group');

// Conecta ao broker
$conf->set('metadata.broker.list', 'kafka:9092');

// Importante: ler desde o início se não houver offset salvo
$conf->set('auto.offset.reset', 'earliest');

// Configuração opcional para auto commit
$conf->set('enable.auto.commit', 'true');

// Conectar ao Kafka broker
$rk = new RdKafka\KafkaConsumer($conf);
$rk->subscribe(['mysql.comments_db.comments']);

// Loop infinito para ler mensagens
echo "📡 Aguardando mensagens no tópico 'meu-topico'...\n";

while (true) {
    $message = $rk->consume(120 * 1000); // timeout 120s
    if ($message === null) continue;

    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            $payload = json_decode($message->payload, true);
            if (isset($payload['after'])) {
                $data = $payload['after'];
                file_put_contents('oimundo.txt', print_r($payload, true), FILE_APPEND);
                echo "Atualizado comentário ID {$data['id']}\n";
            }
            file_put_contents('oimundo.txt', print_r($payload, true), FILE_APPEND);
            echo "oi mundo";
            break;

        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            // fim da partição
            break;

        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            break;

        default:
            echo "Erro: {$message->errstr()}\n";
            break;
    }
}


