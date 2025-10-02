<?php

namespace App\Controller\Form;

use App\Repository\RecipeRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;

class ImportRecipeType extends AbstractType
{
    public function __construct(private readonly RecipeRepository $recipeRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('slug', TextType::class, [
            'label' => 'Recipe Slug',
            'required' => true,
            'attr' => [
                'placeholder' => 'e.g., chocolate-cake'
            ],
            'constraints' => [
                new Callback(function ($slug, $context) {
                    if ($this->recipeRepository->findOneBy(['slug' => $slug])) {
                        $context
                            ->buildViolation('A recipe with this slug already exists.')
                            ->addViolation();
                    }
                })
            ]
        ]);
    }
}