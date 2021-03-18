<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class ClientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param Array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $client = $options['client'];

        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'empty_data' => $client->getName()
            ])

            ->add('email', EmailType::class, [
                'required' => false,
                'empty_data' => $client->getEmail()
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'client' => ''
        ]);
    }
}
