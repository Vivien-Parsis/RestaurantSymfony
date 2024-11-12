<?php 

namespace App\Form;

use App\Entity\Plat;
use App\Entity\Menu;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlatType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Plat Name',
            ])
            ->add('ingredients', TextType::class, [
                'label' => 'Ingredients',
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Price',
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Category',
            ])
            ->add('menu', ChoiceType::class, [
                'label' => 'Menu',
                'choices' => $options['menus'],
                'choice_label' => function ($menu) {
                    return $menu->getName();
                },
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Add Plat',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Plat::class,
            'menus' => [],
        ]);
    }
}