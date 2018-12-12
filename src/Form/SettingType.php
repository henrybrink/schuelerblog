<?php
namespace App\Form;

use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class TinymceType extends AbstractType {

    /* ONLY THE LAYOUT SHOULD BE CHANGED, NOT THE CLASS AT SELF, SO I ONLY NEED TO IMPLEMENT THE PARENT CLASS */
    public function configureOptions(OptionsResolver $resolver) {
        $resolver->setDefaults([
            'settingsType' => 'text',
        ]);
    }

    public function getParent() {
        return TextareaType::class;
    }
}