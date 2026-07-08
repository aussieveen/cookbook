<?php

declare(strict_types=1);

namespace App\Controller\Form;

use App\Controller\Admin\RecipeCrudController;
use App\Message\ParseRecipeImagesMessage;
use App\Service\ImageUploader;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class ParseRecipeImagesController extends AbstractController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly ImageUploader $imageUploader,
        private readonly MessageBusInterface $bus,
    ) {
    }

    #[Route(path: 'admin/parse-images', name: 'admin_recipe_parse_images', methods: ['GET', 'POST'])]
    public function parse(Request $request): Response
    {
        $form = $this->createForm(ParseRecipeImagesType::class)
            ->add('Parse', SubmitType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile[] $files */
            $files = $form->get('images')->getData();

            $s3Keys = array_map(fn(UploadedFile $f) => $this->imageUploader->upload($f), $files);

            $this->bus->dispatch(new ParseRecipeImagesMessage($s3Keys));

            $this->addFlash('success', 'Images submitted for parsing. The recipe will appear under "Pending Approval" when ready.');

            return $this->redirect(
                $this->adminUrlGenerator
                    ->setController(RecipeCrudController::class)
                    ->setAction('index')
                    ->generateUrl()
            );
        }

        return $this->render('admin/parse_recipe_images.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
