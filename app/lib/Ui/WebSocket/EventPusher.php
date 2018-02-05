<?php

namespace Honeygavi\Ui\WebSocket;

use Exception;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;
use SplObjectStorage;
use Trellis\Common\BaseObject;

class EventPusher extends BaseObject implements MessageComponentInterface
{
    protected $clients;

    public function __construct()
    {
        $this->clients = new SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $connection)
    {
        error_log(__METHOD__ . " - " . $connection->resourceId);

        $this->clients->attach($connection);
    }

    public function onMessage(ConnectionInterface $from, $message)
    {
        error_log(__METHOD__ . " - " . $from->resourceId . ' - ' . $message);

        // @todo route to either all or specific clients based on $message['event_channel']

        foreach ($this->clients as $client) {
            if ($from !== $client) {
                $client->send($message);
            }
        }
    }

    public function onClose(ConnectionInterface $connection)
    {
        error_log(__METHOD__ . " - " . $connection->resourceId);

        $this->clients->detach($connection);
    }

    public function onError(ConnectionInterface $connection, Exception $error)
    {
        error_log(__METHOD__ . " - " . $connection->resourceId);

        $conn->close();
    }

    public function onNewEvent($event_message)
    {
        error_log(__METHOD__ . " - " . $event_message);

        $msg_data = json_decode($event_message, true);

        // @todo route to websocket and/or pgm subscribers based on $msg_data['channel']

        foreach ($this->clients as $client) {
            $client->send(json_encode($msg_data['event']));
        }

        // @todo add support for pragmatic general multicast via zmq
    }
}
