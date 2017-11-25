<?php

namespace App\Helpers;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

class BaseController implements ContainerAwareInterface
{

    /** @var  ContainerInterface */
    protected $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param string $id
     *
     * @return object
     */
    protected function get(string $id)
    {
        return $this->container->get($id);
    }

    protected function renderResponse(string $template, array $context = [], array $headers = [], int $statusCode = 200)
    {
        return new Response($this->get('twig')->render($template, $context), $statusCode, $headers);
    }
}