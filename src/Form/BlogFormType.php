<?php

namespace App\Form;

use App\Entity\Blog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BlogFormType extends AbstractType {

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
		$builder
			->add('title', TextType::class, ['attr' => ['class' => 'form-control']])
			->add('urlName', TextType::class, ['attr' => ['class' => 'form-control']])
			->add('about', TextareaType::class, ['required' => false, 'attr' => ['class' => 'form-control']])
			->add('save', SubmitType::class, ['label' => 'Create', 'attr' => ['class' => 'btn btn-primary mt-3']])
			->getForm()
		;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Blog::class,
        ]);
    }
}
