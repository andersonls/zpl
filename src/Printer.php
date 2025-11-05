<?php

namespace Zpl;

class Printer
{
    /**
     * @var resource
     */
    protected $socket;

    public function __construct(string $host, int $port = 9100)
    {
        $this->connect($host, $port);
    }

    /**
     * Destroy an instance.
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Create an instance statically.
     */
    public static function printer(string $host, int $port = 9100): self
    {
        return new static($host, $port);
    }

    /**
     * Connect to printer.
     *
     *
     * @throws CommunicationException if the connection fails.
     */
    protected function connect(string $host, int $port): void
    {
        $this->socket = @socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if (! $this->socket || ! @socket_connect($this->socket, $host, $port)) {
            $error = $this->getLastError();
            throw new CommunicationException($error['message'], $error['code']);
        }
    }

    /**
     * Close connection to printer.
     */
    protected function disconnect(): void
    {
        @socket_close($this->socket);
    }

    /**
     * Send ZPL data to printer.
     *
     * @throws CommunicationException if writing to the socket fails.
     */
    public function send(string $zpl): void
    {
        if (! @socket_write($this->socket, $zpl)) {
            $error = $this->getLastError();
            throw new CommunicationException($error['message'], $error['code']);
        }
    }

    /**
     * Get the last socket error.
     */
    protected function getLastError(): array
    {
        $code = socket_last_error($this->socket);
        $message = socket_strerror($code);

        return compact('code', 'message');
    }

    /**
     * Queries the connected printer and returns a PrinterStatus object
     */
    public function getPrinterStatus(): ?PrinterStatus
    {
        if (! $this->socket) {
            return null;
        }

        $this->send('~HS');

        if (! @socket_recv($this->socket, $response, 96, 0)) {
            return null;
        }

        return PrinterStatus::createFromRawResponse($response);
    }
}
