<?php


namespace inisire\ReactBundle\HttpFoundation;


use Psr\Http\Message\StreamInterface;
use React\Stream\ReadableStreamInterface;
use Symfony\Component\HttpFoundation\Response;

class StreamedResponse extends Response
{
    /**
     * @var StreamInterface
     */
    private $stream;

    public function __construct(StreamInterface $stream, int $status = 200, array $headers = [])
    {
        parent::__construct(null, $status, $headers);
        $this->stream = $stream;
    }

    /**
     * @return StreamInterface
     */
    public function getStream()
    {
        return $this->stream;
    }
}