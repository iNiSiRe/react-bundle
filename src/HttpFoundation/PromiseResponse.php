<?php


namespace inisire\ReactBundle\HttpFoundation;


use React\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\Response;

class PromiseResponse extends Response
{
    /**
     * @var PromiseInterface
     */
    private $promise;

    public function __construct(PromiseInterface $promise, int $status = 200, array $headers = [])
    {
        parent::__construct(null, $status, $headers);
        $this->promise = $promise;
    }

    /**
     * @return PromiseInterface
     */
    public function getPromise(): PromiseInterface
    {
        return $this->promise;
    }
}