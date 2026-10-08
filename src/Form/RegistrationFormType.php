<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RegistrationFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre nom.'),
                    new Length(max: 100),
                ],
            ])
            ->add('prenom', TextType::class, [
                'mapped' => false,
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre prénom.'),
                    new Length(max: 100),
                ],
            ])
            ->add('telephone', TelType::class, [
                'mapped' => false,
                'required' => false,
                'constraints' => [
                    new Regex(pattern: '/^\+?[0-9 .-]{6,15}$/', message: 'Numéro de téléphone invalide.'),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer votre adresse email.'),
                    new Email(message: 'Adresse email invalide.'),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue(message: 'Vous devez accepter les conditions d\'utilisation.'),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // lu et haché dans le contrôleur
                'mapped' => false,
                'attr' => ['autocomplete' => 'new-password'],
                'constraints' => [
                    new NotBlank(message: 'Veuillez entrer un mot de passe.'),
                    new Length(min: 8, max: 4096, minMessage: 'Votre mot de passe doit contenir au moins {{ limit }} caractères.'),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
