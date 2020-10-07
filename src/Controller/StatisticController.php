<?php

namespace inisire\ReactBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

class StatisticController
{
    /**
     * @Route(path="/server/statistic", methods={"GET"})
     */
    public function memory()
    {
        return new JsonResponse([
            'memory_usage' => sprintf("%f", memory_get_usage(true) / 1024 / 1024)
        ]);
    }
}
