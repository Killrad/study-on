<?php

namespace App\Form;


use App\DTO\CredentialsDTO;
use App\DTO\ShortUSerDTO;
use App\DTO\UserDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', EmailType::class, [
                'label' => 'Email',
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => "Email ножет быть пустым!"
                    ]),
                    new Email([
                        'message' => "Некорректный email"
                    ])
                ]
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'invalid_message' => 'Пароли не совпадают',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Пароль не может быть пустым'
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Пароль должен быть длиннее {{ limit }} символов',
                    ])
                ],
                'first_name' => 'password',
                'second_name' => 'password_repeat',
                'first_options' => [
                    'label' => 'Пароль'
                ],
                'second_options' => [
                    'label' => 'Подтвердите пароль'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ShortUSerDTO::class,
        ]);
    }
}