<?php


namespace PitouFW\Entity;


use PitouFW\Core\Entity;

class User extends Entity {
    private string $email = '';
    private string $passwd = '';
    private string $jam_id = '';
    private int $admin = 0;
    private ?int $reg_timestamp = null;

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
     * @return int
     */
    public function getRegTimestamp(): int {
        return $this->reg_timestamp;
    }

    /**
     * @param int $reg_timestamp
     * @return User
     */
    public function setRegTimestamp(int $reg_timestamp): User {
        $this->reg_timestamp = $reg_timestamp;
        return $this;
    }
}