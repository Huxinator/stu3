<?php

declare(strict_types=1);

namespace Stu\Component\Player\Register;

use Laminas\Mail\Exception\RuntimeException;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Sendmail;
use Noodlehaus\ConfigInterface;
use Stu\Orm\Entity\UserInterface;

final class RegistrationMobileSender implements RegistrationMobileSenderInterface
{
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function send(UserInterface $player, int $mobile): void
    {
        $body = <<<EOT
        Hallo %s\n\n
        Dies ist der Registrierungstoken für Star Trek Universe:\n
        %s\n\n
        Das STU-Team\n\n
        EOT;

        $mail = new Message();
        $mail->addTo(($player->getMobile())($this->config->get('smsapi.email_sender_address')));
        $mail->setSubject(_('Star Trek Universe - Anmeldung'));
        $mail->setFrom($this->config->get('game.email_sender_address'));
        $mail->setBody(
            sprintf(
                $body,
                $player->getLogin(),
                $mobiletoken,
            )
        );
        try {
            $transport = new Sendmail();
            $transport->send($mail);
        } catch (RuntimeException $e) {
            return;
        }
    }
}
