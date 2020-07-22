<?php


namespace PitouFW\Entity;


use PitouFW\Core\Entity;

class PasswdReset extends Entity {
    private ?int $user_id = null;
    private string $token = '';
    private ?string $requested_at = null;
    private ?string $used_at = null;

    public static function getTableName(): string {
        return 'passwd_reset';
    }

    /**
     * @return int|null
     */
    public function getUserId(): ?int {
        return $this->user_id;
    }

    /**
     * @param int|null $user_id
     * @return PasswdReset
     */
    public function setUserId(?int $user_id): PasswdReset {
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
     * @return PasswdReset
     */
    private function setUser(User $user): PasswdReset {
        $this->user = $user;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken(): string {
        return $this->token;
    }

    /**
     * @param string $token
     * @return PasswdReset
     */
    public function setToken(string $token): PasswdReset {
        $this->token = $token;
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
     * @return PasswdReset
     */
    public function setRequestedAt(?string $requested_at): PasswdReset {
        $this->requested_at = $requested_at;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUsedAt(): ?string {
        return $this->used_at;
    }

    /**
     * @param string|null $used_at
     * @return PasswdReset
     */
    public function setUsedAt(?string $used_at): PasswdReset {
        $this->used_at = $used_at;
        return $this;
    }
}