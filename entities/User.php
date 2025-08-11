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

    /**
     * @param int $ttl
     * @return bool
     */
    public function login(int $ttl = UserModel::SESSION_CACHE_TTL_DEFAULT): bool {
        if (!self::exists('id', $this->getId())) {
            return false;
        }

        $session_token = UserModel::generateSessionToken();
        $redis = new Redis();
        $cache_key = UserModel::SESSION_CACHE_PREFIX . $session_token;
        $cookie_set = setcookie(UserModel::SESSION_COOKIE_NAME, $session_token, time() + $ttl, WEBROOT, PROD_HOST);
        $redis_set = $redis->set($cache_key, $this->getId(), $ttl);

        return $cookie_set && $redis_set;
    }

    /**
     *
     */
    public function startAccountValidation(): void {
        $token = Utils::generateToken();
        $redis = new Redis();
        $cache_key = UserModel::ACCOUNT_VALIDATION_CACHE_PREFIX . $token;
        $redis->set($cache_key, $this->getId(), UserModel::ACCOUNT_VALIDATION_CACHE_TTL);

        $mailer = new Mailer();
        $mailer->queueMail(
            $this->getEmail(),
            \L::register_email_subject(NAME),
            'mail/' . t()->getAppliedLang() . '/account_validation',
            ['token' => $token],
        );
    }

    /**
     * @return bool
     */
    public function isAwaitingEmailConfirmation(): bool {
        $req = DB::get()->prepare("
            SELECT confirmed_at
            FROM email_update
            WHERE user_id = ?
            AND new_email = ?
            ORDER BY requested_at DESC
            LIMIT 1
        ");
        $req->execute([$this->getId(), $this->getEmail()]);
        $rep = $req->fetch();

        return $rep !== false && $rep['confirmed_at'] === null;
    }

    /**
     * @return bool
     */
    public function isTrustable(): bool {
        return !TRUST_NEEDED || ($this->isActive() && !$this->isAwaitingEmailConfirmation());
    }

    public function getLastEmailUpdate(): ?EmailUpdate {
        $req = DB::get()->prepare("
            SELECT id
            FROM email_update
            WHERE user_id = ?
            AND new_email = ?
            ORDER BY requested_at DESC
            LIMIT 1
        ");
        $req->execute([$this->getId(), $this->getEmail()]);
        $rep = $req->fetch();

        return $rep !== false ? EmailUpdate::read($rep['id']) : null;
    }

    /**
     *
     */
    public function startNewMailValidation(): void {
        do {
            $token = Utils::generateToken();
        } while (EmailUpdate::exists('confirm_token', $token));

        $email_update = new EmailUpdate();
        $email_update->setUserId($this->getId())
            ->setConfirmToken($token)
            ->setOldEmail($this->getEmail())
            ->setNewEmail($_POST['email'])
            ->save();

        $mailer = new Mailer();
        $mailer->queueMail(
            $_POST['email'],
            \L::profile_email_subject,
            'mail/' . t()->getAppliedLang() . '/newmail',
            ['token' => $token],
        );

        UserModel::logout();
        $this->login();
    }
}