<?php

namespace inisire\ReactBundle\Proxy;


use Psr\Http\Message\UploadedFileInterface;
use React\EventLoop\LoopInterface;
use React\Http\Io\UploadedFile;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

class UploadedFileProxy implements UploadedFileInterface
{
    /**
     * @var UploadedFile
     */
    private $file;

    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * UploadedFileProxy constructor.
     *
     * @param UploadedFile  $file
     * @param LoopInterface $loop
     */
    public function __construct(UploadedFile $file, LoopInterface $loop)
    {
        $this->file = $file;
        $this->loop = $loop;
    }

    /**
     * @inheritdoc
     */
    public function getStream()
    {
        return $this->file->getStream();
    }

    /**
     * @inheritdoc
     */
    public function moveTo($targetPath)
    {
        $readable = new ReadableResourceStream($this->file->getStream()->detach(), $this->loop);
        $writable = new WritableResourceStream(fopen($targetPath, 'w'), $this->loop);
        $readable->pipe($writable);
        $readable->close();
        $writable->close();
    }

    /**
     * @inheritdoc
     */
    public function getSize()
    {
        return $this->file->getSize();
    }

    /**
     * @inheritdoc
     */
    public function getError()
    {
        return $this->file->getError();
    }

    /**
     * @inheritdoc
     */
    public function getClientFilename()
    {
        return $this->file->getClientFilename();
    }

    /**
     * @inheritdoc
     */
    public function getClientMediaType()
    {
        return $this->file->getClientMediaType();
    }
}