<?php

namespace App\Form;

use App\Entity\DoodleComment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class DoodleCommentFormType extends AbstractType
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
        $builder
            ->add('doodleId', HiddenType::class, [
                'mapped' => false,
            ])
            ->add('content', TextareaType::class,
                [
                    'label'  => false,
                    'required' => true,
                ])
            ->add('save', SubmitType::class, [
                    'attr' => ['class' => 'btn-artflow mt-4 float-end'],
                    'label' => $this->translator->trans('Add comment')
                ]
            )
        ;
    }

    /**
     *
     */
    public function validate($data, ExecutionContextInterface $context): void
    {
        if ( $this->authorization->isGranted('ROLE_USER') == false ) {
            $context->buildViolation($this->translator->trans('You have to be logged to add comment'))
                ->atPath('content')
                ->addViolation();
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DoodleComment::class,
            'constraints' => [
                new Callback([$this, 'validate']),
            ],
        ]);
    }
}
