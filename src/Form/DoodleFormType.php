<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoodleFormType extends AbstractType
{
    private $translator;
    private $authorization;

    public function __construct(TranslatorInterface $translator, AuthorizationCheckerInterface $auth)
    {
        $this->authorization = $auth;
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (isset($options['data']['id']) && is_numeric($options['data']['id'])) {
            $builder
                ->add('id', HiddenType::class);
        } else {
            $builder
                ->add('tempDir', HiddenType::class)
                ->add('sourceDoodle', HiddenType::class)
                ->add('sourceDoodleId', HiddenType::class);
        }

        $builder
            ->add('title', TextType::class)
            ->add('description', TextareaType::class)
            ->add('submit', SubmitType::class, [
                'attr' => ['class' => 'btn-artflow mt-4 float-right'],
            ]);
    }
}
