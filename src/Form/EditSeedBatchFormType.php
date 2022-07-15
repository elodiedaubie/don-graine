<?php

namespace App\Form;

use App\Entity\Plant;
use App\Entity\Quality;
use App\Entity\SeedBatch;
use App\Repository\PlantRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class EditSeedBatchFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            //number constraints is set on entity
            ->add('seedQuantity', IntegerType::class, [
                'label' => 'Nombre de graines par lot',
                'rounding_mode' => 1,
            ])
            ->add('plant', EntityType::class, [
                'class' => Plant::class,
                'choice_label' => 'name',
                'label' => 'Nom de l\'espèce',
                'placeholder' => 'Choisir une plante',
                'query_builder' => function (PlantRepository $plantRepository) {
                    return $plantRepository->createQueryBuilder('p')
                        ->orderBy('p.name', 'ASC');
                }
            ])
            ->add('quality', EntityType::class, [
                'class' => Quality::class,
                'choice_label' => 'name',
                'label' => 'Qualité de graine',
                'expanded' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => SeedBatch::class,
        ]);
    }
}
