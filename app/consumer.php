<?php

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
$rk->subscribe(['meu-topico']);

// Loop infinito para ler mensagens
echo "📡 Aguardando mensagens no tópico 'meu-topico'...\n";

while (true) {
    $message = $rk->consume(120*1000); // timeout 120s
    switch ($message->err) {
        case RD_KAFKA_RESP_ERR_NO_ERROR:
            echo "📥 Mensagem recebida: {$message->payload}\n";
            break;
        case RD_KAFKA_RESP_ERR__PARTITION_EOF:
            echo "🕐 Fim da partição\n";
            break;
        case RD_KAFKA_RESP_ERR__TIMED_OUT:
            echo "⏱️ Tempo limite\n";
            break;
        default:
            echo "❌ Erro: {$message->errstr()}\n";
            break;
    }
}
