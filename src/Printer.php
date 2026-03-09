<?php

namespace Zpl;

class Printer
{
    /**
     * @var \Socket|false
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
        return new self($host, $port);
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
            throw new CommunicationException((string) $error['message'], (int) $error['code']);
        }
    }

    /**
     * Close connection to printer.
     */
    protected function disconnect(): void
    {
        if (! $this->socket) {
            return;
        }
        @socket_close($this->socket);
    }

    /**
     * Send ZPL data to printer.
     *
     * @throws CommunicationException if writing to the socket fails.
     */
    public function send(string $zpl): void
    {
        if (! $this->socket || ! @socket_write($this->socket, $zpl)) {
            $error = $this->getLastError();
            throw new CommunicationException((string) $error['message'], (int) $error['code']);
        }
    }

    /**
     * Get the last socket error.
     *
     * @return array<string,string|int>
     */
    protected function getLastError(): array
    {
        if (! $this->socket) {
            $code = 0;
            $message = 'The communication socket is not open';
        } else {
            $code = socket_last_error($this->socket);
            $message = socket_strerror($code);
        }

        return compact('code', 'message');
    }

    /**
     * Queries the connected printer and returns a PrinterStatus object
     *
     * @throws CommunicationException if writing to the socket fails.
     */
    public function getPrinterStatus(): ?PrinterStatus
    {
        $socket = $this->socket;
        if (! $socket) {
            return null;
        }

        $this->send('~HS');

        socket_set_option($socket, SOL_SOCKET, SO_RCVTIMEO, ['sec' => 3, 'usec' => 0]);

        if (! @socket_recv($socket, $response, 1024, 0)) {
            return null;
        }

        return PrinterStatus::createFromRawResponse($response);
    }
}
