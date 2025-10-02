<?php

declare(strict_types=1);

namespace App\Controller\Admin;

use App\Entity\Recipe;
use App\Service\ImageUploader;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class RecipeCrudController extends AbstractCrudController
{
    public function __construct(
        private ImageUploader $imageUploader,
        #[Autowire('%s3.bucket.name%')]
        private string $s3BucketName,
        #[Autowire('%s3.bucket.region%')]
        private string $s3Region,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Recipe::class;
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
            TextareaField::new('description'),
            ImageField::new('image')
                ->setUploadDir('public/uploads/images')
                ->setBasePath(sprintf(
                    'https://%s.s3.%s.amazonaws.com/images/',
                    $this->s3BucketName,
                    $this->s3Region
                ))
                ->setRequired(false)
                ->setLabel('Image')
                ->setFormTypeOption('upload_new', function ($uploadedFile) {
                    if ($uploadedFile instanceof UploadedFile) {
                        return $this->imageUploader->upload($uploadedFile);
                    }
                    return null;
                }),
            BooleanField::new('mastered'),
            CollectionField::new('components')
                ->useEntryCrudForm(ComponentCrudController::class)
                ->setLabel('Components')
                ->hideOnIndex(),
            CollectionField::new('steps')
                ->useEntryCrudForm(StepCrudController::class)
                ->setLabel('Steps')
                ->hideOnIndex(),
            CollectionField::new('mistakes')
                ->useEntryCrudForm(MistakeCrudController::class)
                ->setLabel('Mistakes')
                ->hideOnIndex(),
        ];
    }

    public function configureActions(Actions $actions): Actions
    {

        $importAction = Action::new('import', 'Import Recipe')
            ->linkToRoute('admin_recipe_import')
            ->createAsGlobalAction()
            ->setCssClass('btn btn-primary');

        $actions->add(Crud::PAGE_INDEX, $importAction);

        return $actions;
    }
}
