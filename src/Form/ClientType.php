<?php
namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ClientType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param Array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class,[
                    "label" => "Nom",
                    "attr" => [
                        "placeholder" => "Nom"
                    ]
                ]
            )
        ;
    }
}
