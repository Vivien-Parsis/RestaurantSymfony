<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
    public function show(\Throwable $exception): Response
    {
        if ($exception instanceof NotFoundHttpException) {
            $template = 'error/404.html.twig';
            $message = 'The page you are looking for could not be found.';
        } else {
            $template = 'error/500.html.twig';
            $message = 'An unexpected error has occurred. Please try again later.';
        }

        return $this->render($template, [
            'message' => $message,
        ]);
    }
}