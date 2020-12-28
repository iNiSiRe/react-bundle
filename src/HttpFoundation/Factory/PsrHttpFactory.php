<?php


namespace inisire\ReactBundle\HttpFoundation\Factory;


use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PsrHttpFactory extends \Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory
{
    public function createResponse(Response $symfonyResponse)
    {
        $response = parent::createResponse($symfonyResponse);
        
        if ($symfonyResponse instanceof \inisire\ReactBundle\HttpFoundation\StreamedResponse) {
            $response = $response->withBody($symfonyResponse->getStream());   
        }
        
        return $response;
    }
}