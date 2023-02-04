<?php

	namespace App\Form;

	use App\Entity\User;
	use Symfony\Component\Form\AbstractType;
	use Symfony\Component\Form\FormBuilderInterface;
	use Symfony\Component\Form\Extension\Core\Type\TextType;
	use Symfony\Component\Form\Extension\Core\Type\PasswordType;
	use Symfony\Component\Form\Extension\Core\Type\EmailType;
	use Symfony\Component\Form\Extension\Core\Type\SubmitType;
	use Symfony\Component\OptionsResolver\OptionsResolver;

	class UserAddType extends AbstractType
	{
		public function buildForm( FormBuilderInterface $builder, array $options ): void
		{
			$builder
				->add(
					'login',
					TextType::class,
					[
						'required'  => true,
						'trim'      => true,
						'label'     => 'Login',
						'help'      => 'Please enter your login',
						'help_html' => true,
						'attr'      =>
							[
								'minlength' => 2,
								'maxlength' => 32,
							],
					]
				)
				->add(
					'password',
					PasswordType::class,
					[
						'required'  => true,
						'trim'      => true,
						'label'     => 'Password',
						'help'      => 'Please enter your password',
						'help_html' => true,
						'attr'      =>
							[
								'minlength' => 6,
								'maxlength' => 255,
							],
					]
				)
				->add(
					'email',
					EmailType::class,
					[
						'required'  => true,
						'trim'      => true,
						'label'     => 'Email',
						'help'      => 'Please enter your email',
						'help_html' => true,
						'attr'      =>
							[
								'minlength' => 2,
								'maxlength' => 64,
							],
					]
				)
				->add(
					'lastname',
					TextType::class,
					[
						'required'  => true,
						'trim'      => true,
						'label'     => 'Lastname',
						'help'      => 'Please enter your lastname',
						'help_html' => true,
						'attr'      =>
							[
								'minlength' => 2,
								'maxlength' => 64,
							],
					]
				)
				->add(
					'firstname',
					TextType::class,
					[
						'required'  => true,
						'trim'      => true,
						'label'     => 'Firstname',
						'help'      => 'Please enter your firstname',
						'help_html' => true,
						'attr'      =>
							[
								'minlength' => 2,
								'maxlength' => 64,
							],
					]
				)
				->add( 'save', SubmitType::class );
		}

		public function configureOptions( OptionsResolver $resolver ): void
		{
			$resolver->setDefaults(
				[
					'data_class' => User::class,
				]
			);
		}
	}
