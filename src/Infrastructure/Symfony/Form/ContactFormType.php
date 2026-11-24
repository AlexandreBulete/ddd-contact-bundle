<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form;

use AlexandreBulete\DddContactBundle\Infrastructure\Spam\FormTiming;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * The public contact form. Its layout belongs to the application; the trap
 * field hides itself, so that a theme forgetting it cannot expose it.
 *
 * Three unmapped fields serve the checks: `website` (a trap a person never
 * fills), `issued` (when the form was shown, signed) and `recaptcha` (the
 * token, when reCAPTCHA is on).
 */
final class ContactFormType extends AbstractType
{
    public function __construct(
        private readonly FormTiming $timing,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'contact.form.name', 'attr' => ['autocomplete' => 'name']])
            ->add('email', EmailType::class, ['label' => 'contact.form.email', 'attr' => ['autocomplete' => 'email']])
            ->add('subject', TextType::class, ['label' => 'contact.form.subject', 'required' => false])
            ->add('message', TextareaType::class, ['label' => 'contact.form.message', 'attr' => ['rows' => 8]])
            ->add('website', TextType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'contact.form.trap',
                'attr' => ['autocomplete' => 'off', 'tabindex' => '-1'],
                'row_attr' => ['aria-hidden' => 'true', 'style' => 'position:absolute;left:-10000px;width:1px;height:1px;overflow:hidden'],
            ])
            ->add('issued', HiddenType::class, ['mapped' => false, 'data' => $this->timing->issue()]);

        if ($options['recaptcha'] === true) {
            $builder->add('recaptcha', HiddenType::class, ['mapped' => false, 'attr' => ['data-contact-recaptcha' => '']]);
        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactFormData::class,
            'translation_domain' => 'messages',
            'recaptcha' => false,
            'attr' => ['data-contact-form' => ''],
        ]);
        $resolver->setAllowedTypes('recaptcha', 'bool');
    }
}
