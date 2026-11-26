<?php

declare(strict_types=1);

namespace AlexandreBulete\DddContactBundle\Infrastructure\Symfony;

use AlexandreBulete\DddAntispamBundle\SpamGuard;
use AlexandreBulete\DddAntispamBundle\SpamPolicy;
use AlexandreBulete\DddAntispamBundle\Submission;
use AlexandreBulete\DddAntispamBundle\Verdict;
use AlexandreBulete\DddContactBundle\Application\Command\ReceiveContactMessage\ReceiveContactMessageCommand;
use AlexandreBulete\DddContactBundle\Domain\ValueObject\ContactMessageId;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\ContactFormData;
use AlexandreBulete\DddContactBundle\Infrastructure\Symfony\Form\ContactFormType;
use AlexandreBulete\DddFoundation\Application\Command\CommandBusInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
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
    /** Signs the form's timing token and names its reCAPTCHA action. */
    public const string PURPOSE = 'contact';

    private SpamPolicy $policy;

    public function __construct(
        private FormFactoryInterface $forms,
        private SpamGuard $guard,
        private CommandBusInterface $commandBus,
        private TranslatorInterface $translator,
        #[Autowire(service: 'limiter.contact_form')]
        RateLimiterFactoryInterface $limiter,
        #[Autowire(param: 'contact.spam.min_seconds')]
        int $minSeconds,
        #[Autowire(param: 'contact.spam.max_age')]
        int $maxAge,
    ) {
        $this->policy = new SpamPolicy(self::PURPOSE, $limiter, $minSeconds, $maxAge);
    }

    /**
     * @param array<string, mixed> $options
     *
     * @return FormInterface<mixed>
     */
    public function create(array $options = []): FormInterface
    {
        return $this->forms->create(ContactFormType::class, new ContactFormData(), $options);
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

        $verdict = $this->guard->judge(Submission::fromForm($form->get('antispam'), $request), $this->policy);
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
}
