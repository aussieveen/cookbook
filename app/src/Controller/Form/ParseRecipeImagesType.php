<?php

declare(strict_types=1);

namespace App\Controller\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ParseRecipeImagesType extends AbstractType
{
    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('images', FileType::class, [
            'label' => 'Recipe Images',
            'multiple' => true,
            'mapped' => false,
            'required' => true,
            'attr' => ['accept' => 'image/*'],
            'constraints' => [
                new All([
                    new File([
                        'maxSize' => '8M',
                        'maxSizeMessage' => 'This image is too large ({{ size }} {{ suffix }}). '
                            . 'Please resize it to under {{ limit }} {{ suffix }} before uploading.',
                        'mimeTypes' => ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
                        'mimeTypesMessage' => 'Please upload a valid image (JPEG, PNG, WEBP, GIF)',
                    ]),
                ]),
            ],
        ]);
    }
}
