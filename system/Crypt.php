<?php
/**
 * Created by PhpStorm.
 * User: Peter
 * Date: 04/08/2017
 * Time: 14:57
 */

class Crypt {
    public static function encrypt($data, $password, $method = 'aes-192-cbc') {
        $wasItSecure = false;
        $len = openssl_cipher_iv_length($method);
        $iv = openssl_random_pseudo_bytes($len, $wasItSecure);
        if ($wasItSecure) {
            $cipher = openssl_encrypt($data, $method, $password, OPENSSL_RAW_DATA, $iv);
            return $iv.$cipher;
        } else {
            return false;
        }
    }

    public static function decrypt($data, $password, $method = 'aes-192-cbc') {
        $len = openssl_cipher_iv_length($method);
        $iv = substr($data, 0, $len);
        $cipher = substr($data, $len);
        $clear = openssl_decrypt($cipher, $method, $password, OPENSSL_RAW_DATA, $iv);
        return $clear;
    }
}