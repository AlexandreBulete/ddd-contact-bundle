# ddd-contact-bundle

A contact form as a reusable DDD Bounded Context for Symfony: messages are
**kept** and read in a Sylius back office, robots are kept out without a third
party, and whoever should know is told through
[ddd-notification-bundle](https://github.com/AlexandreBulete/ddd-notification-bundle)
— on the channel of their choice, without the message itself.

## Installation

```bash
composer require alexandrebulete/ddd-contact-bundle
bin/console doctrine:migrations:migrate
```

The Flex recipe registers the bundle. The `contact_message` and
`contact_settings` tables come with the bundle's own migration.

## Model

| Aggregate | Holds |
|---|---|
| `ContactMessage` | sender name and email, optional subject, body, status (new, read, archived), spam flag |
| `ContactSettings` | business settings edited in the back office: the address a full copy is forwarded to |

The database is the source of truth. Email is only a copy: a message received
while SMTP is down is still there.

## Use cases

Commands: `ReceiveContactMessage` (`#[PublicAccess]`), `FileContactMessage`,
`FlagContactMessage`, `DeleteContactMessage`, `ChangeContactSettings`.

Queries: `FindContactMessages`, `FindContactMessage`,
`CountNewContactMessages`, `FindContactSettings`.

Permissions follow the skeleton's convention: `contact.find_contact_messages`,
`contact.change_contact_settings`…

## The public form

```php
#[Route('/contact', name: 'contact', methods: ['GET', 'POST'])]
public function __invoke(Request $request, ContactForm $contact): Response
{
    $form = $contact->create();
    if (ContactOutcome::Sent === $contact->handle($form, $request)) {
        return $this->redirectToRoute('contact', ['sent' => 1]);
    }

    return $this->render('contact.html.twig', ['form' => $form]);
}
```

Render the form as you like; keep `form_rest(form)` (hidden fields) and, when
reCAPTCHA is enabled, `{{ include('@DddContact/recaptcha.html.twig') }}` after
it. `handle()` answers `Pending`, `Invalid`, `TooMany` (an error is on the
form) or `Sent`.

## Spam

On by default, nothing sent to a third party:

- a **trap field**, hidden from people, that robots fill;
- a **minimal delay** between showing the form and sending it, carried by a
  token signed with `kernel.secret` (a token older than `max_age` is refused);
- a **rate limit** per client address (sliding window, application cache).

A robot is thanked like anyone and nothing is kept. Behind a reverse proxy,
configure `framework.trusted_proxies` so that the client address is the
visitor's.

**reCAPTCHA v3**, invisible, is optional and adds to these checks. A low score
keeps the message, flagged as spam, rather than dropping it. It loads a
Google script: mention it in your privacy policy.

## Being told

When ddd-notification-bundle is installed, the bundle declares the topic
`contact.message_received` and publishes a notice for each message that is
not spam: *a message arrived*, with a link to it in the back office — never
the sender or the content. Recipients and channels are subscriptions of that
bundle. Without it, nothing changes.

A **full copy** by email is optional too: set an address in the back office
(**Contact → Settings**), the sender becomes the reply-to address.

## Configuration

```yaml
contact:
    mail:
        from: '%env(CONTACT_MAIL_FROM)%'   # sender of the copies; empty = no copy
    spam:
        min_seconds: 3
        max_age: 86400
        rate_limit: { limit: 5, interval: '1 hour' }
        recaptcha:
            enabled: false
            site_key: '%env(RECAPTCHA_SITE_KEY)%'
            secret: '%env(RECAPTCHA_SECRET)%'
            min_score: 0.5
    admin:
        enabled: true                      # Sylius screens; false for a headless setup
        grid_limits: [20, 50, 100]
```

Transport and secrets stay in the environment (`MAILER_DSN`, reCAPTCHA keys);
the back office only holds business choices.

## Seams

Ports bound in `config/services.php`, to rebind in the application:

| Port | Default | Rebind for |
|---|---|---|
| `DomainEventPublisherInterface` | `ImmediateEventPublisher` (in-process, in the transaction) | an outbox |
| `ContactMessageRepositoryInterface` | Doctrine | another storage |
| `ContactSettingsRepositoryInterface` | Doctrine | another storage |

## Development

```bash
composer qa    # phpstan (max, strict) + deptrac + phpunit
```

Integration tests run on SQLite in memory, or on the database given by
`DDD_TEST_DATABASE_URL` (CI: PostgreSQL, MySQL, SQLite).

## License

MIT
