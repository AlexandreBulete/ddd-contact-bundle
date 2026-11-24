<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony;

use AlexandreBulete\DddContactBundle\Application\Command\ReceiveContactMessage\ReceiveContactMessageCommand;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\Submission;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\SubmissionGuard;
use AlexandreBulete\DddContactBundle\Infrastructure\Spam\Verdict;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\ContactFormData;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\ContactFormType;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * The public side of the bundle, for the application's controller:
 *
 *     $form = $contactForm->create();
 *     if ($contactForm->handle($form, $request) === ContactOutcome::Sent) {
 *         return $this->redirectToRoute('contact_thanks');
 *     }
 *     return $this->render('contact.html.twig', ['form' => $form]);
 */
final readonly class ContactForm
{
    public function __construct(
        private FormFactoryInterface $forms,
        private SubmissionGuard $guard,
        private CommandBusInterface $commandBus,
        private TranslatorInterface $translator,
        #[Autowire(param: 'contact.spam.recaptcha.site_key')]
        private ?string $recaptchaSiteKey = null,
    ) {}

    /**
     * @param array<string, mixed> $options
     *
     * @return FormInterface<mixed>
     */
    public function create(array $options = []): FormInterface
    {
        return $this->forms->create(ContactFormType::class, new ContactFormData(), $options + ['recaptcha' => $this->recaptchaSiteKey !== null]);
    }

    /**
     * @param FormInterface<mixed> $form
     */
    public function handle(FormInterface $form, Request $request): ContactOutcome
    {
        $form->handleRequest($request);
        if (!$form->isSubmitted()) {
            return ContactOutcome::Pending;
        }
        if (!$form->isValid()) {
            return ContactOutcome::Invalid;
        }

        $verdict = $this->guard->judge(new Submission(
            trap: self::field($form, 'website'),
            timingToken: self::field($form, 'issued'),
            clientIp: $request->getClientIp(),
            recaptchaToken: self::field($form, 'recaptcha'),
        ));
        if ($verdict === Verdict::TooMany) {
            $form->addError(new FormError($this->translator->trans('contact.form.too_many', [], 'validators')));

            return ContactOutcome::TooMany;
        }
        if ($verdict === Verdict::Robot) {
            return ContactOutcome::Sent;
        }

        $data = $form->getData();
        \assert($data instanceof ContactFormData);
        $this->commandBus->dispatch(new ReceiveContactMessageCommand(
            id: ContactMessageId::generate(),
            senderName: (string) $data->name,
            senderEmail: (string) $data->email,
            subject: $data->subject,
            body: (string) $data->message,
            spam: $verdict === Verdict::Suspicious,
        ));

        return ContactOutcome::Sent;
    }

    /**
     * @param FormInterface<mixed> $form
     */
    private static function field(FormInterface $form, string $name): ?string
    {
        if (!$form->has($name)) {
            return null;
        }
        $value = $form->get($name)->getData();

        return is_string($value) ? $value : null;
    }
}
