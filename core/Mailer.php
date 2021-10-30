<?php

namespace PitouFW\Core;

use Exception;
use JetBrains\PhpStorm\ArrayShape;
use PHPMailer\PHPMailer\PHPMailer;
use PitouFW\Entity\EmailQueue;
use PitouFW\Model\EmailModel;

class Mailer extends PHPMailer {
    const SEND_AS_DEFAULT = EMAIL_SEND_AS_DEFAULT;

    public function __construct() {
        parent::__construct(true);

        $this->IsSMTP();
        $this->CharSet = parent::CHARSET_UTF8;
        $this->SMTPDebug  = 0;
        $this->SMTPAuth   = true;
        $this->SMTPSecure = SMTP_SECURE;
        $this->Host       = SMTP_HOST;
        $this->Port       = SMTP_PORT;
        $this->Username   = SMTP_USER;
        $this->Password   = SMTP_PASS;

    }

    /**
     * @param string $contact
     * @return array
     */
    #[ArrayShape(['email' => "string", 'name' => "string"])]
    private static function getContactDetailsFromString(string $contact): array {
        if (filter_var(trim($contact), FILTER_VALIDATE_EMAIL)) {
            return [
                'email' => trim($contact),
                'name' => ''
            ];
        }

        $split = explode(' ', $contact);
        $contact_email = trim($split[count($split) - 1], '<>');
        unset($split[count($split) - 1]);
        $contact_name = implode(' ', $split);

        return [
            'email' => $contact_email,
            'name' => $contact_name
        ];
    }

    /**
     * @param array $email
     */
    public function sendMail(array $email): void {
        $sender = $email['sender'] !== '' ? $email['sender'] : self::SEND_AS_DEFAULT;
        $bcc = json_decode($email['bcc']);

        $contact_from = self::getContactDetailsFromString($sender);
        $contact_to = self::getContactDetailsFromString($email['recipient']);
        array_walk($bcc, function(&$item, $key) {
            $item = self::getContactDetailsFromString($item);
        });

        $sent_at = null;
        $error = null;

        try {
            $this->isHtml(true);
            $this->setFrom($contact_from['email'], $contact_from['name']);
            $this->addAddress($contact_to['email'], $contact_to['name']);
            foreach ($bcc as $contact) {
                $this->addBCC($contact['email'], $contact['name']);
            }

            $this->Subject = $email['subject'];
            $apiCall = new ApiCall(true);
            $apiCall->setUrl('mailer/' . $email['id'] . '?render_key=' . EmailModel::hashId($email['id']))
                ->exec();
            $body = $apiCall->responseText();
            $this->Body = $body;

            $text = html_entity_decode($body);
            $text = strip_tags($text);
            $text = trim($text);
            $text = preg_replace("#(\s*\\n){3,}#", "\n\n", $text);
            $text = preg_replace("#( ){2,}#", "", $text);
            $text = wordwrap($text);
            $this->AltBody = $text;

            $is_sent = $this->send();
            if ($is_sent) {
                $sent_at = Utils::datetime();
            } else {
                $error = $this->ErrorInfo;
                Logger::logError('PHPMailer error: ' . $this->ErrorInfo);
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
            Logger::logError('PHPMailer exception: ' . $e->getMessage());
        }

        $req = DB::get()->prepare("UPDATE email_queue SET sent_at = ?, error = ? WHERE id = ?");
        $req->execute([$sent_at, $error, $email['id']]);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $template
     * @param array $params
     * @param array $bcc
     * @param string $from
     * @throws \ReflectionException
     */
    public function queueMail(string $to, string $subject, string $template = 'mail/' . DEFAULT_LANGUAGE . '/default', array $params = [], array $bcc = [], string $from = self::SEND_AS_DEFAULT): void {
        $email_queue = new EmailQueue();
        $email_queue->setSender($from)
            ->setRecipient($to)
            ->setSubject($subject)
            ->setTemplate($template)
            ->setParams(json_encode($params))
            ->setBcc(json_encode($bcc))
            ->save();
    }
}
