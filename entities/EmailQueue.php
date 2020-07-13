<?php


namespace PitouFW\Entity;


use PitouFW\Core\Entity;

class EmailQueue extends Entity {
    private string $sender;
    private string $recipient;
    private string $subject;
    private string $template;
    private string $params;
    private string $bcc;
    private string $created_at;
    private ?string $sent_at;
    private ?string $error;

    /**
     * @return string
     */
    public static function getTableName(): string {
        return 'email_queue';
    }

    /**
     * @return string
     */
    public function getSender(): string {
        return $this->sender;
    }

    /**
     * @param string $sender
     * @return EmailQueue
     */
    public function setSender(string $sender): EmailQueue {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return string
     */
    public function getRecipient(): string {
        return $this->recipient;
    }

    /**
     * @param string $recipient
     * @return EmailQueue
     */
    public function setRecipient(string $recipient): EmailQueue {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return string
     */
    public function getSubject(): string {
        return $this->subject;
    }

    /**
     * @param string $subject
     * @return EmailQueue
     */
    public function setSubject(string $subject): EmailQueue {
        $this->subject = $subject;
        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate(): string {
        return $this->template;
    }

    /**
     * @param string $template
     * @return EmailQueue
     */
    public function setTemplate(string $template): EmailQueue {
        $this->template = $template;
        return $this;
    }

    /**
     * @return array
     */
    public function getParams(): string {
        return $this->params;
    }

    /**
     * @param array $params
     * @return EmailQueue
     */
    public function setParams(string $params): EmailQueue {
        $this->params = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function getBcc(): string {
        return $this->bcc;
    }

    /**
     * @param array $bcc
     * @return EmailQueue
     */
    public function setBcc(string $bcc): EmailQueue {
        $this->bcc = $bcc;
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string {
        return $this->created_at;
    }

    /**
     * @param string $created_at
     * @return EmailQueue
     */
    public function setCreatedAt(string $created_at): EmailQueue {
        $this->created_at = $created_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSentAt(): ?string {
        return $this->sent_at;
    }

    /**
     * @param string|null $sent_at
     * @return EmailQueue
     */
    public function setSentAt(?string $sent_at): EmailQueue {
        $this->sent_at = $sent_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getError(): ?string {
        return $this->error;
    }

    /**
     * @param string|null $error
     * @return EmailQueue
     */
    public function setError(?string $error): EmailQueue {
        $this->error = $error;
        return $this;
    }
}