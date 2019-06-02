<?php

namespace inisire\ReactBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class StatisticController extends AbstractController
{
    /**
     * @Route("/server/statistic")
     * @Method({"GET"})
     */
    public function indexAction()
    {
        return $this->render('ReactBundle:Default:index.html.twig');
    }
}
