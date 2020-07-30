<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 07/08/2019
 * Time: 00:11
 */

namespace PitouFW\Model;


use PitouFW\Core\DB;
use PitouFW\Core\Mailer;
use PitouFW\Core\Redis;
use PitouFW\Core\Utils;
use PitouFW\Entity\User;
use function PitouFW\Core\t;

class UserModel {
    const SESSION_COOKIE_NAME = 'PTFW_SESSID';
    const SESSION_CACHE_PREFIX = 'session_';
    const SESSION_CACHE_TTL_DEFAULT = 86400; // 1 day
    const SESSION_CACHE_TTL_LONG = 86400 * 366; // 1 year

    const ACCOUNT_VALIDATION_CACHE_PREFIX = 'valid_';
    const ACCOUNT_VALIDATION_CACHE_TTL = 86400; // 1 day

    private static ?User $user = null;

    public static function hashInfo(string $info): string {
        return hash('sha512', $info);
    }

    public static function generateSessionToken(): string {
        return sha1(uniqid());
    }

    public static function login(User $user, int $ttl = self::SESSION_CACHE_TTL_DEFAULT): bool {
        if (!User::exists('id', $user->getId())) {
            return false;
        }

        $session_token = self::generateSessionToken();
        $redis = new Redis();
        $cache_key = self::SESSION_CACHE_PREFIX . $session_token;
        $cookie_set = setcookie(self::SESSION_COOKIE_NAME, $session_token, Utils::time() + $ttl, WEBROOT, PROD_HOST);
        $redis_set = $redis->set($cache_key, $user->getId(), $ttl);

        return $cookie_set && $redis_set;
    }

    private static function getCachedValue() {
        $redis = new Redis();
        $cache_key = self::SESSION_CACHE_PREFIX . Utils::secure($_COOKIE[self::SESSION_COOKIE_NAME] ?? '');
        return $redis->get($cache_key);
    }

    public static function isLogged(): bool {
        if (self::$user !== null) {
            return true;
        }

        if (isset($_COOKIE[self::SESSION_COOKIE_NAME])) {
            $uid = self::getCachedValue();
            return User::exists('id', $uid) && $uid !== false;
        }

        return false;
    }

    public static function rejectGuests(): void {
        if (!self::isLogged()) {
            header('location: ' . WEBROOT . 'login');
            die;
        }
    }

    public static function rejectUsers(): void {
        if (self::isLogged()) {
            header('location: ' . WEBROOT);
            die;
        }
    }

    public static function get(): ?User {
        if (self::isLogged()) {
            if (self::$user === null) {
                $uid = self::getCachedValue();
                if (User::exists('id', $uid)) {
                    self::$user = User::read($uid);
                }
            }

            return self::$user;
        }

        return null;
    }

    public static function validatePassword(string $passwd): bool {
        $lower = preg_match("#[a-z]+#", $passwd) === 1;
        $upper = preg_match("#[A-Z]+#", $passwd) === 1;
        $digit = preg_match("#[0-9]+#", $passwd) === 1;
        $length = strlen($passwd) >= PASSWD_MINIMAL_LENGTH;

        return $lower && $upper && $digit && $length;
    }

    public static function hashPassword(string $passwd): string {
        return password_hash($passwd, PASSWORD_DEFAULT);
    }

    public static function checkPassword(string $passwd, string $hash): bool {
        return password_verify($passwd, $hash);
    }

    public static function logout() {
        $session_token = $_COOKIE[self::SESSION_COOKIE_NAME] ?? null;

        if ($session_token !== null) {
            setcookie(self::SESSION_COOKIE_NAME, '', -1, WEBROOT, PROD_HOST);
            $redis = new Redis();
            $cache_key = self::SESSION_CACHE_PREFIX . $session_token;
            $res = $redis->del($cache_key);

            return $res > 0;
        }

        return true;
    }

    public static function isPasswdResetTokenValid(string $token): bool {
        $minus_24h = Utils::time() - 86400;
        $datetime_to_compare_with = date('Y-m-d H:i:s', $minus_24h);

        $req = DB::get()->prepare("
            SELECT COUNT(*) AS cnt
            FROM passwd_reset
            WHERE token = ?
            AND used_at IS NULL
            AND requested_at > ?
        ");
        $req->execute([$token, $datetime_to_compare_with]);
        $res = $req->fetch();

        return $res['cnt'] > 0;
    }

    public static function startAccountValidation(User $user): void {
        $token = Utils::generateToken();
        $redis = new Redis();
        $cache_key = self::ACCOUNT_VALIDATION_CACHE_PREFIX . $token;
        $redis->set($cache_key, $user->getId(), self::ACCOUNT_VALIDATION_CACHE_TTL);

        $mailer = new Mailer();
        $mailer->queueMail(
            $user->getEmail(),
            \L::register_email_subject(NAME),
            'mail/' . t()->getAppliedLang() . '/account_validation',
            ['token' => $token],
        );
    }

    public static function isAwaitingEmailConfirmation(User $user): bool {
        $req = DB::get()->prepare("
            SELECT confirmed_at
            FROM email_update
            WHERE user_id = ?
            ORDER BY requested_at DESC
            LIMIT 1
        ");
        $req->execute([$user->getId()]);
        $rep = $req->fetch();

        return $rep !== false && $rep['confirmed_at'] === null;
    }

    public static function isTrustable(User $user): bool {
        return !TRUST_NEEDED || ($user->isActive() && !self::isAwaitingEmailConfirmation($user));
    }
}