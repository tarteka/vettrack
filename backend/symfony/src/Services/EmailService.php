<?php

namespace App\Services;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class EmailService
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $fromEmail,
        private readonly string $fromName
    ) {}

    /**
     * Envia un email HTML.
     * @param string      $to       Destinatario
     * @param string      $subject  Asunto
     * @param string      $html     Contenido HTML
     * @param string|null $text     Contenido de texto plano (opcional)
     * @param string|null $from     Email del remitente (opcional)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @throws TransportExceptionInterface
     */
    public function sendHtml(
        string $to,
        string $subject,
        string $html,
        ?string $text = null,
        ?string $from = null,
        ?string $fromName = null
    ) : void
    {
        // configuración del email
        $email = (new Email())
            ->from(new Address($from ?? $this->fromEmail, $fromName ?? $this->fromName))
            ->to(new Address($to))
            ->subject($subject)
            ->html($html)
            ->text($text ?? strip_tags($html));

        $this->mailer->send($email);
    }

    /**
     * Envia un email con plantilla Twig.
     *
     * @param string      $to       Destinatario
     * @param string      $subject  Asunto
     * @param string      $templatePath Ruta de la plantilla Twig
     * @param array       $context  Contexto de la plantilla
     * @param string|null $from     Email del remitente (opcional)
     * @param string|null $fromName Nombre del remitente (opcional)
     * @throws TransportExceptionInterface
     */
    public function sendTemplate(
        string $to,
        string $subject,
        string $templatePath,
        array $context = [],
        ?string $from = null,
        ?string $fromName = null
    ): void {
        $email = (new TemplatedEmail())
            ->from(new Address($from ?? $this->fromEmail, $fromName ?? $this->fromName))
            ->to(new Address($to))
            ->subject($subject)
            ->htmlTemplate($templatePath)
            ->context($context);

        $this->mailer->send($email);
    }

    /**
     * Envia un email con plantilla Twig y un PDF adjunto.
     *
     * @param string $to Destinatario
     * @param string $subject Asunto
     * @param string $templatePath Ruta de la plantilla Twig
     * @param array $context Contexto de la plantilla
     * @param string $pdfContent Contenido del PDF en binario
     * @param string $filename Nombre del archivo PDF adjunto
     * @throws TransportExceptionInterface
     */
    public function sendTemplateWithPdf(
        string $to,
        string $subject,
        string $templatePath,
        array $context,
        string $pdfContent,
        string $filename
    ): void {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($to)
            ->subject($subject)
            ->htmlTemplate($templatePath)
            ->context($context)
            ->text('Le enviamos su factura en PDF. Si tiene dudas, contacte con la clínica.')
            ->attach($pdfContent, $filename, 'application/pdf');

        $this->mailer->send($email);
    }

}