<?php

namespace App\Controller\Form;

use App\Controller\Admin\RecipeCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImportRecipeController extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ImportRecipeFormHandler $formHandler
    ) {
    }

    #[Route(path: 'admin/import', name: 'admin_recipe_import', methods: ['GET','POST'])]
    public function import(Request $request): Response
    {
        $form = $this->createForm(ImportRecipeType::class)
            ->add('Import', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->formHandler->handle($data['slug']);
            // Handle the import logic here, e.g., fetch recipe by slug and save to database

            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(RecipeCrudController::class)
                    ->setAction('index')
                    ->generateUrl()
            );
        }

        return $this->render('admin/import_recipe.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
