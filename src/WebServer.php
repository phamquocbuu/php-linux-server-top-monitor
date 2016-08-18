<?php
namespace wapmorgan\ServerStat;

use \Exception;

class WebServer {
    public $address;
    public $port;
    private $socket;
    private $configuration;

    public function __construct() {
        if (!is_resource($this->socket = socket_create(AF_INET, SOCK_STREAM, getprotobyname("tcp")))) {
            throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
        }
    }

    public function bind($address = null, $port = null) {
        if (!is_null($address))
            $this->address = $address;
        if (!is_null($port))
            $this->port = $port;
        if (!socket_bind($this->socket, $this->address, $this->port)) {
            throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
        }
    }

    public function listen($backlog) {
        if (!socket_listen($this->socket, $backlog)) {
            throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
        }
    }

    public function await() {
        $socket = socket_accept($this->socket);
        if (!$socket)
            throw new Exception(socket_strerror(socket_last_error()), socket_last_error());
        return $socket;
    }

    public function close() {
        return socket_close($this->socket);
    }
}
