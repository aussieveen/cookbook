<?php

declare(strict_types=1);

namespace App\Form;

use App\Entity\IngredientName;
use App\Repository\IngredientNameRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IngredientNameType extends AbstractType
{
    public function __construct(
        private readonly IngredientNameRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event): void {
            $value = $event->getData();
            // Non-numeric value = new name typed by user; convert to entity ID for EntityType
            if ($value !== null && $value !== '' && !ctype_digit((string) $value)) {
                $entity = $this->repository->findOrCreate($value);
                if ($entity->getId() === null) {
                    // ponytail: flush here so EntityType's transformer gets a valid ID
                    $this->em->flush();
                }
                $event->setData((string) $entity->getId());
            }
        });
    }

    /** @SuppressWarnings(PHPMD.UnusedFormalParameter) */
    public function buildView(FormView $view, FormInterface $form, array $options): void
    {
        $view->vars['attr']['data-ea-widget'] = 'ea-autocomplete';
        $view->vars['attr']['data-ea-autocomplete-allow-item-create'] = 'true';
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => IngredientName::class,
            'choice_label' => 'name',
        ]);
    }

    public function getParent(): string
    {
        return EntityType::class;
    }
}
