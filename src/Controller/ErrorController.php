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
            $message = 'Page non trouvée - Erreur 404';
        } else {
            $template = 'error/500.html.twig';
            $message = 'Le serveur a rencontré une erreur inattendue. Veuillez réessayer plus tard.';
        }

        return $this->render($template, [
            'message' => $message,
        ]);
    }
}
