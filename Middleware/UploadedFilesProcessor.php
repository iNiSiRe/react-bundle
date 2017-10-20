<?php

namespace inisire\ReactBundle\Middleware;


use inisire\ReactBundle\Proxy\UploadedFileProxy;
use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\LoopInterface;
use React\Http\UploadedFile;
use React\Stream\ReadableResourceStream;
use React\Stream\WritableResourceStream;

class UploadedFilesProcessor
{
    /**
     * @var LoopInterface
     */
    private $loop;

    /**
     * UploadedFilesProcessor constructor.
     *
     * @param LoopInterface $loop
     */
    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
    }

    /**
     * @param $file
     *
     * @return UploadedFileProxy|array
     */
    private function recursiveSwapFiles(&$file)
    {
        if ($file instanceof UploadedFile) {
            $file = new UploadedFileProxy($file, $this->loop);
        } elseif (is_array($file)) {
            foreach ($file as $key => &$nextFile) {
                $this->recursiveSwapFiles($nextFile);
            }
        }

        return $file;
    }

    /**
     * @param ServerRequestInterface $request
     * @param                        $next
     *
     * @return mixed
     */
    public function __invoke(ServerRequestInterface $request, $next)
    {
        /** @var UploadedFile[] $uploadedFiles */
        $files = $request->getUploadedFiles();
        $this->recursiveSwapFiles($files);

        return $next($request->withUploadedFiles($files));
    }
}