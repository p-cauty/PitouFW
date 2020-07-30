<?php


namespace PitouFW\Entity;


use PitouFW\Core\DB;
use PitouFW\Core\Entity;
use PitouFW\Core\Mailer;
use PitouFW\Core\Redis;
use PitouFW\Core\Utils;
use PitouFW\Model\UserModel;
use function PitouFW\Core\t;

class User extends Entity {
    private string $email = '';
    private string $passwd = '';
    private string $jam_id = '';
    private int $admin = 0;
    private ?string $reg_timestamp = null;
    private ?string $activated_at = null;

    /**
     * @return string
     */
    public static function getTableName(): string {
        return 'user';
    }

    /**
     * @return string
     */
    public function getEmail(): string {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getPasswd(): string {
        return $this->passwd;
    }

    /**
     * @param string $passwd
     * @return User
     */
    public function setPasswd(string $passwd): User {
        $this->passwd = $passwd;
        return $this;
    }

    /**
     * @return string
     */
    public function getJamId(): string {
        return $this->jam_id;
    }

    /**
     * @param string $jam_id
     * @return User
     */
    public function setJamId(string $jam_id): User {
        $this->jam_id = $jam_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getAdmin(): int {
        return $this->admin;
    }

    public function isAdmin(): bool {
        return !!$this->getAdmin();
    }

    /**
     * @param int $admin
     * @return User
     */
    public function setAdmin(int $admin): User {
        $this->admin = $admin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegTimestamp(): ?string {
        return $this->reg_timestamp;
    }

    /**
     * @param string|null $reg_timestamp
     * @return User
     */
    public function setRegTimestamp(?string $reg_timestamp): User {
        $this->reg_timestamp = $reg_timestamp;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getActivatedAt(): ?string {
        return $this->activated_at;
    }

    /**
     * @return bool
     */
    public function isActive(): bool {
        return $this->getActivatedAt() !== null;
    }

    /**
     * @param string|null $activated_at
     * @return User
     */
    public function setActivatedAt(?string $activated_at): User {
        $this->activated_at = $activated_at;
        return $this;
    }
}