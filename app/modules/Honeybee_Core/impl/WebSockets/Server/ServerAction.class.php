<?php

use Honeygavi\Agavi\App\Base\Action;
use Honeygavi\Ui\WebSocket\EventPusher;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server;
use React\ZMQ\Context;

class Honeybee_Core_WebSockets_ServerAction extends Action
{
    public function execute(AgaviRequestDataHolder $request_data)
    {
        $event_loop = EventLoopFactory::create();
        $event_pusher = new EventPusher();

        $context = new Context($event_loop);
        $pull_socket = $context->getSocket(ZMQ::SOCKET_PULL);

        $pull_socket->bind(
            sprintf(
                'tcp://%s:%s',
                "0.0.0.0",
                AgaviConfig::get('websocket.inbound_port')
            )
        );

        // sent by command handlers via zmq
        $pull_socket->on('message', array($event_pusher, 'onNewEvent'));

        $web_socket = new Server($event_loop);
        $web_socket->listen(
            AgaviConfig::get('websocket.outbound_port'),
            "0.0.0.0"
        );

        $web_server = new IoServer(
            new HttpServer(
                new WsServer($event_pusher)
            ),
            $web_socket
        );

        $event_loop->run();

        return AgaviView::NONE;
    }

    public function isSecure()
    {
        return false;
    }
}
