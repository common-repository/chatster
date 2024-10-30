<?php

namespace Chatster\Core;

if ( ! defined( 'ABSPATH' ) ) exit;

class Crypto
{
    const METHOD = 'AES256';

    public static function encrypt($string)
    {
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize);

        $ciphertext = openssl_encrypt(
            $string,
            self::METHOD,
            CHATSTER_KEY,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $nonce.$ciphertext;
    }

    public static function decrypt($string)
    {

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($string, 0, $nonceSize, '8bit');
        $ciphertext = mb_substr($string, $nonceSize, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            CHATSTER_KEY,
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext;
    }
}
