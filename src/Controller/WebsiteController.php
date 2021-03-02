<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;


class WebsiteController extends AbstractController
{
    /**
     * @Route("/welcome", name="app_homepage")
     */
    public function index()
    {
        return $this->render('website/welcome.html.twig');
    }

}
