<?php

$conf = new RdKafka\Conf();
$conf->set('metadata.broker.list', 'kafka:9092');

$producer = new RdKafka\Producer($conf);
$producer->addBrokers("kafka:9092");

$topic = $producer->newTopic("meu-topico");

echo "✉️  Enviando mensagens para o tópico 'meu-topico'...\n";
echo "Digite uma mensagem e pressione Enter (Ctrl+C para sair):\n";

while (true) {
    $msg = trim(fgets(STDIN));

    if ($msg === '') {
        echo "⚠️  Mensagem vazia ignorada.\n";
        continue;
    }

    $topic->produce(RD_KAFKA_PARTITION_UA, 0, $msg);
    $producer->flush(10000); // aguarda envio
    echo "✅ Mensagem enviada: {$msg}\n";
}
