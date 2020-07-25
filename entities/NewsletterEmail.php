<?php


namespace PitouFW\Entity;


use PitouFW\Core\Entity;

class NewsletterEmail extends Entity {
    private string $email = '';
    private ?string $created_at = null;

    /**
     * @return string
     */
    public static function getTableName(): string {
        return 'newsletter_email';
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @param string $email
     * @return NewsletterEmail
     */
    public function setEmail(string $email): NewsletterEmail {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreatedAt(): ?string {
        return $this->created_at;
    }

    /**
     * @param string|null $created_at
     * @return NewsletterEmail
     */
    public function setCreatedAt(?string $created_at): NewsletterEmail {
        $this->created_at = $created_at;
        return $this;
    }
}