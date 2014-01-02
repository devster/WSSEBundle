<?php

namespace Devster\WSSEBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('DevsterWSSEBundle:Default:index.html.twig', array('name' => $name));
    }
}
