<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route( '/user', name: 'users_list', methods: [ 'GET' ] )]
    public function index(): Response
    {
        return $this->render(
            'user/index.html.twig',
            []
        );
    }
}
