<?php

namespace App\Controller;

use App\Helpers\BaseController;
use App\Service\Scraper;
use Spatie\Crawler\Crawler;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    public function index(Request $request)
    {
        /** @var FormBuilder $formbuilder */
        $formbuilder = $this->get('form.factory')->createBuilder();

        $form = $formbuilder->create('inputform', null, ['compound' => true])->getForm();
        $form->add('sourceurl', UrlType::class);

        $form->add('submit', SubmitType::class);

        if ($request->getMethod() === 'POST') {
            $form->handleRequest($request);




        }


        return $this->renderResponse('pages/index.twig', ['form' => $form->createView()]);
    }
}