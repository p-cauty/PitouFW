<?php


namespace PitouFW\Entity;


class EmailUpdate extends \PitouFW\Core\Entity {
    private ?int $user_id = null;
    private string $confirm_token = '';
    private string $old_email = '';
    private string $new_email = '';
    private ?string $requested_at = null;
    private ?string $confirmed_at = null;

    /**
     * @return string
     */
    public static function getTableName(): string {
        return 'email_update';
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int {
        return $this->user_id;
    }

    /**
     * @param int|null $user_id
     * @return EmailUpdate
     */
    public function setUserId(?int $user_id): EmailUpdate {
        $this->user_id = $user_id;
        if (User::exists('id', $user_id)) {
            $this->setUser(User::read($user_id));
        }

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User {
        return $this->user ?? null;
    }

    /**
     * @param User $user
     * @return EmailUpdate
     */
    private function setUser(User $user): EmailUpdate {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getConfirmToken(): string {
        return $this->confirm_token;
    }

    /**
     * @param string $confirm_token
     * @return EmailUpdate
     */
    public function setConfirmToken(string $confirm_token): EmailUpdate {
        $this->confirm_token = $confirm_token;
        return $this;
    }

    /**
     * @return string
     */
    public function getOldEmail(): string {
        return $this->old_email;
    }

    /**
     * @param string $old_email
     * @return EmailUpdate
     */
    public function setOldEmail(string $old_email): EmailUpdate {
        $this->old_email = $old_email;
        return $this;
    }

    /**
     * @return string
     */
    public function getNewEmail(): string {
        return $this->new_email;
    }

    /**
     * @param string $new_email
     * @return EmailUpdate
     */
    public function setNewEmail(string $new_email): EmailUpdate {
        $this->new_email = $new_email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRequestedAt(): ?string {
        return $this->requested_at;
    }

    /**
     * @param string|null $requested_at
     * @return EmailUpdate
     */
    public function setRequestedAt(?string $requested_at): EmailUpdate {
        $this->requested_at = $requested_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getConfirmedAt(): ?string {
        return $this->confirmed_at;
    }

    /**
     * @param string|null $confirmed_at
     * @return EmailUpdate
     */
    public function setConfirmedAt(?string $confirmed_at): EmailUpdate {
        $this->confirmed_at = $confirmed_at;
        return $this;
    }
}