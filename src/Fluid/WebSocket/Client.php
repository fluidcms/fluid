<?php
/**
 * Very basic websocket client.
 * Supporting draft hybi-10.
 *
 * @author Simon Samtleben <web@lemmingzshadow.net>
 * @version 2011-10-18
 */
namespace Fluid\WebSocket;

use Fluid\Config;
use React;
use React\EventLoop\StreamSelectLoop;

class Client
{
    const PATH = '/fluidcms/';
    const TOKEN_LENGHT = 16;

    const MESSAGE_TYPEID_WELCOME = 0;
    const MESSAGE_TYPEID_PREFIX = 1;
    const MESSAGE_TYPEID_CALL = 2;
    const MESSAGE_TYPEID_CALL_RESULT = 3;
    const MESSAGE_TYPEID_CALL_ERROR = 4;
    const MESSAGE_TYPEID_SUBSCRIBE = 5;
    const MESSAGE_TYPEID_UNSUBSCRIBE = 6;
    const MESSAGE_TYPEID_PUBLISH = 7;
    const MESSAGE_TYPEID_EVENT = 8;

    /** @var string $key */
    private $key;

    /** @var StreamSelectLoop $loop */
    private $loop;

    /** @var string $host */
    private $host;

    /** @var int $port */
    private $port;

    /** @var React\Socket\Connection $socket */
    private $socket;

    private $connected = false;

    /**
     * @param StreamSelectLoop $loop
     */
    public function __construct(StreamSelectLoop $loop)
    {
        $this->loop = $loop;

        $this->setPort(Config::get('websocket'))
            ->setKey($this->generateToken());
    }

    public function __destruct()
    {
        //$this->disconnect();
    }

    /**
     * Create header for websocket client
     *
     * @return string
     */
    private function createHeader()
    {
        return "GET " . self::PATH . " HTTP/1.1" . "\r\n" .
            "Origin: this" . "\r\n" .
            "Host: {$this->getHost()}" . "\r\n" .
            "Sec-WebSocket-Key: {$this->getKey()}" . "\r\n" .
            "User-Agent: PHP " . phpversion() . "\r\n" .
            "Upgrade: websocket" . "\r\n" .
            "Sec-WebSocket-Protocol: wamp" . "\r\n" .
            "Connection: Upgrade" . "\r\n" .
            "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";
    }

    /**
     * Connect client to server
     *
     * @param string $host
     * @param int $port
     */
    public function connect($host = "127.0.0.1", $port = null)
    {
        if (null !== $port) {
            $this->setPort($port);
        } else {
            $port = $this->getPort();
        }

        $this->setHost($host);

        $client = stream_socket_client("tcp://{$host}:{$port}");
        $this->setSocket(new React\Socket\Connection($client, $this->loop));
        $this->socket->pipe(new React\Stream\Stream(STDOUT, $this->loop));
        $this->socket->on('data', function ($data) {
            echo '';
        });
        $this->socket->write($this->createHeader());
    }


    /**
     * Generate token
     *
     * @return string
     */
    private function generateToken()
    {
        $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!"§$%&/()=[]{}';
        $useChars = array();
        // select some random chars:
        for ($i = 0; $i < self::TOKEN_LENGHT; $i++) {
            $useChars[] = $characters[mt_rand(0, strlen($characters) - 1)];
        }
        // Add numbers
        array_push($useChars, rand(0, 9), rand(0, 9), rand(0, 9));
        shuffle($useChars);
        $randomString = trim(implode('', $useChars));
        $randomString = substr($randomString, 0, self::TOKEN_LENGHT);

        return base64_encode($randomString);
    }

    /**
     * @param int $port
     * @return self
     */
    public function setPort($port)
    {
        $this->port = (int)$port;
        return $this;
    }

    /**
     * @return int
     */
    public function getPort()
    {
        return $this->port;
    }

    /**
     * @param React\Socket\Connection $socket
     * @return self
     */
    public function setSocket(React\Socket\Connection $socket)
    {
        $this->socket = $socket;
        return $this;
    }

    /**
     * @return React\Socket\Connection
     */
    public function getSocket()
    {
        return $this->socket;
    }

    /**
     * @param string $host
     * @return self
     */
    public function setHost($host)
    {
        $this->host = (string)$host;
        return $this;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $key
     * @return self
     */
    public function setKey($key)
    {
        $this->key = (string)$key;
        return $this;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    public function oldconnect($host, $port, $path, $origin = false)
    {
        $this->key = base64_encode($this->generateRandomString(16, false, true));

        $header = "GET {$path} HTTP/1.1" . "\r\n" .
            ($origin ? "Origin: {$origin}" . "\r\n" : "") .
            "Host: {$host}" . "\r\n" .
            "Sec-WebSocket-Key: {$this->key}" . "\r\n" .
            "User-Agent: PHP " . phpversion() . "\r\n" .
            "Upgrade: websocket" . "\r\n" .
            "Sec-WebSocket-Protocol: wamp" . "\r\n" .
            "Connection: Upgrade" . "\r\n" .
            "Sec-WebSocket-Version: 13" . "\r\n" . "\r\n";

        $this->socket = fsockopen($host, $port, $errno, $errstr, 2);
        socket_set_timeout($this->socket, 0, 10000);
        fwrite($this->socket, $header);

        $raw = "";
        while (!feof($this->socket)) {
            $line = fgets($this->socket);

            if ($line !== false) {
                $raw .= $line;
            } else {
                if (!empty($raw)) {
                    $response = $this->parseHeader($raw);
                    $raw = "";
                    $this->parseResponse($response);
                }
            }
        }
    }

    private function parseResponse($response)
    {
        if (!$this->connected && isset($response['Sec-Websocket-Accept'])) {
            if (base64_encode(pack('H*', sha1($this->key . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))) === $response['Sec-Websocket-Accept']) {
                $this->connected = true;
            }
        }

        if ($this->connected && !empty($response['content'])) {
            $content = trim($response['content']);
            if (preg_match('/(\[.*\])/', $content, $match)) {
                $content = json_decode($match[1], true);
                if (is_array($content)) {
                    unset($response['status']);
                    unset($response['content']);
                    $this->receiveData($content, $response);
                }

            }
        }
    }

    private function receiveData($data, $header)
    {
        sleep(1);
        $this->sendData('hello world');
    }

    private function sendData($data, $type = 'text', $masked = true)
    {
        if ($this->connected === false) {
            return false;
        }

        fwrite($this->socket, $this->hybi10Encode('[5,"mytopicyo"]'));

        return true;
    }

    public function disconnect()
    {
        $this->connected = false;
        fclose($this->socket);
    }

    private function parseHeader($header)
    {
        $retval = array();
        $content = "";
        $fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));
        foreach ($fields as $field) {
            if (preg_match('/([^:]+): (.+)/m', $field, $match)) {
                $match[1] = preg_replace('/(?<=^|[\x09\x20\x2D])./e', 'strtoupper("\0")', strtolower(trim($match[1])));
                if (isset($retval[$match[1]])) {
                    $retval[$match[1]] = array($retval[$match[1]], $match[2]);
                } else {
                    $retval[$match[1]] = trim($match[2]);
                }
            } else if (preg_match('!HTTP/1\.\d (\d)* .!', $field)) {
                $retval["status"] = $field;
            } else {
                $content .= $field . "\r\n";
            }
        }
        $retval['content'] = $content;

        return $retval;
    }

    private function hybi10Encode($payload, $type = 'text', $masked = true)
    {
        $frameHead = array();
        $frame = '';
        $payloadLength = strlen($payload);

        switch ($type) {
            case 'text':
                // first byte indicates FIN, Text-Frame (10000001):
                $frameHead[0] = 129;
                break;

            case 'close':
                // first byte indicates FIN, Close Frame(10001000):
                $frameHead[0] = 136;
                break;

            case 'ping':
                // first byte indicates FIN, Ping frame (10001001):
                $frameHead[0] = 137;
                break;

            case 'pong':
                // first byte indicates FIN, Pong frame (10001010):
                $frameHead[0] = 138;
                break;
        }

        // set mask and payload length (using 1, 3 or 9 bytes)
        if ($payloadLength > 65535) {
            $payloadLengthBin = str_split(sprintf('%064b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 255 : 127;
            for ($i = 0; $i < 8; $i++) {
                $frameHead[$i + 2] = bindec($payloadLengthBin[$i]);
            }

            // most significant bit MUST be 0 (close connection if frame too big)
            if ($frameHead[2] > 127) {
                $this->close(1004);
                return false;
            }
        } elseif ($payloadLength > 125) {
            $payloadLengthBin = str_split(sprintf('%016b', $payloadLength), 8);
            $frameHead[1] = ($masked === true) ? 254 : 126;
            $frameHead[2] = bindec($payloadLengthBin[0]);
            $frameHead[3] = bindec($payloadLengthBin[1]);
        } else {
            $frameHead[1] = ($masked === true) ? $payloadLength + 128 : $payloadLength;
        }

        // convert frame-head to string:
        foreach (array_keys($frameHead) as $i) {
            $frameHead[$i] = chr($frameHead[$i]);
        }

        if ($masked === true) {
            // generate a random mask:
            $mask = array();
            for ($i = 0; $i < 4; $i++) {
                $mask[$i] = chr(rand(0, 255));
            }

            $frameHead = array_merge($frameHead, $mask);
        }
        $frame = implode('', $frameHead);
        // append payload to frame:
        for ($i = 0; $i < $payloadLength; $i++) {
            $frame .= ($masked === true) ? $payload[$i] ^ $mask[$i % 4] : $payload[$i];
        }

        return $frame;
    }

    private function hybi10Decode($data)
    {
        if (empty($data)) {
            return null;
        }

        $bytes = $data;
        $dataLength = '';
        $mask = '';
        $coded_data = '';
        $decodedData = '';
        $secondByte = sprintf('%08b', ord($bytes[1]));
        $masked = ($secondByte[0] == '1') ? true : false;
        $dataLength = ($masked === true) ? ord($bytes[1]) & 127 : ord($bytes[1]);

        if ($masked === true) {
            if ($dataLength === 126) {
                $mask = substr($bytes, 4, 4);
                $coded_data = substr($bytes, 8);
            } elseif ($dataLength === 127) {
                $mask = substr($bytes, 10, 4);
                $coded_data = substr($bytes, 14);
            } else {
                $mask = substr($bytes, 2, 4);
                $coded_data = substr($bytes, 6);
            }
            for ($i = 0; $i < strlen($coded_data); $i++) {
                $decodedData .= $coded_data[$i] ^ $mask[$i % 4];
            }
        } else {
            if ($dataLength === 126) {
                $decodedData = substr($bytes, 4);
            } elseif ($dataLength === 127) {
                $decodedData = substr($bytes, 10);
            } else {
                $decodedData = substr($bytes, 2);
            }
        }

        return $decodedData;
    }
}