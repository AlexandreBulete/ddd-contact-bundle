<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddAntispamBundle\Form\AntispamType;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\ContactForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The public contact form. Its layout belongs to the application; the trap
 * field hides itself, so that a theme forgetting it cannot expose it.
 *
 * The checks read the unmapped `antispam` field (ddd-antispam-bundle): a trap,
 * a signed timing token and, when enabled, a reCAPTCHA token.
 */
final class ContactFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'contact.form.name', 'attr' => ['autocomplete' => 'name']])
            ->add('email', EmailType::class, ['label' => 'contact.form.email', 'attr' => ['autocomplete' => 'email']])
            ->add('subject', TextType::class, ['label' => 'contact.form.subject', 'required' => false])
            ->add('message', TextareaType::class, ['label' => 'contact.form.message', 'attr' => ['rows' => 8]])
            ->add('antispam', AntispamType::class, ['purpose' => ContactForm::PURPOSE]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactFormData::class,
            'translation_domain' => 'messages',
            // Kept for callers passing it; reCAPTCHA follows antispam.recaptcha.
            'recaptcha' => false,
            'attr' => ['data-contact-form' => ''],
        ]);
        $resolver->setAllowedTypes('recaptcha', 'bool');
    }
}
