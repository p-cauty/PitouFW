<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 04/08/2017
 * Time: 14:57
 */

namespace PitouFW\Core;

class Crypt {
    const ENCRYPT_RSA_BLOCK_SIZE = 245;
    const DECRYPT_RSA_BLOCK_SIZE = 256;
    const RSA_PADDING = OPENSSL_PKCS1_PADDING;
    const RSA_SIGNATURE_ALGO = OPENSSL_ALGO_SHA512;

    public static function encrypt(string $data, string $password, string $method = 'aes-192-cbc'): ?string {
        $wasItSecure = false;
        $len = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($len, $wasItSecure);
        if ($wasItSecure) {
            $cipher = openssl_encrypt($data, $method, $password, OPENSSL_RAW_DATA, $iv);
            return $iv.$cipher;
        } else {
            return null;
        }
    }

    public static function decrypt(string $data, string $password, string $method = 'aes-192-cbc'): ?string {
        $len = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $len);
        $cipher = substr($data, $len);
        return openssl_decrypt($cipher, $method, $password, OPENSSL_RAW_DATA, $iv) ?? null;
    }

    public static function sign(string $data, string $privkey, string $passphrase = ''): ?string {
        $privkeyid = openssl_pkey_get_private($privkey, $passphrase);
        $isSignatureOk = openssl_sign($data, $signature, $privkeyid, self::RSA_SIGNATURE_ALGO);
        openssl_free_key($privkeyid);

        return $isSignatureOk ? $signature : null;
    }

    public static function verify(string $data, string $signature, string $pubkey): int {
        $pubkeyid = openssl_pkey_get_public($pubkey);
        $res = openssl_verify($data, $signature, $pubkeyid, self::RSA_SIGNATURE_ALGO);
        openssl_free_key($pubkeyid);

        return $res;
    }

    public static function async_encrypt(string $data, string $pubkey): ?string {
        $encrypted = '';
        $plain = str_split($data, self::ENCRYPT_RSA_BLOCK_SIZE);
        $pubkeyid = openssl_pkey_get_public($pubkey);

        foreach ($plain as $chunk) {
            $partialEncrypted = '';
            $isEncryptionOk = openssl_public_encrypt($chunk, $partialEncrypted, $pubkeyid, self::RSA_PADDING);

            if ($isEncryptionOk === false) {
                return null;
            }

            $encrypted .= $partialEncrypted;
        }

        return base64_encode($encrypted);
    }

    public static function async_decrypt(string $data, string $privkey, string $passphrase = ''): ?string {
        $clear = '';
        $encrypted = str_split(base64_decode($data), self::DECRYPT_RSA_BLOCK_SIZE);
        $privkeyid = openssl_pkey_get_private($privkey, $passphrase);

        foreach ($encrypted as $chunk) {
            $partialClear = '';
            $isDecryptionOk = openssl_private_decrypt($chunk, $partialClear, $privkeyid, self::RSA_PADDING);

            if ($isDecryptionOk === false) {
                return null;
            }

            $clear .= $partialClear;
        }

        return $clear;
    }
}
