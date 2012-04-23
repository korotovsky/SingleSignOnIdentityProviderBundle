<?php

namespace FM\SingleSignOnBundle\Encoder;

class OneTimePasswordEncoder
{
    const HASH_DELIMITER = ':';

    private $key;

    public function __construct($key)
    {
        $this->key = $key;
    }

    public function decodeHash($hash)
    {
        return explode(self::HASH_DELIMITER, base64_decode($hash));
    }

    public function encodeHash(array $parts)
    {
        return base64_encode(implode(self::HASH_DELIMITER, $parts));
    }

    public function generateHash($class, $username, $expires, $password)
    {
        return hash('sha256', $class.$username.$expires.$password.$this->key);
    }

    public function compareHashes($hash1, $hash2)
    {
        if (strlen($hash1) !== $c = strlen($hash2)) {
            return false;
        }

        $result = 0;
        for ($i = 0; $i < $c; $i++) {
            $result |= ord($hash1[$i]) ^ ord($hash2[$i]);
        }

        return 0 === $result;
    }

    public function generateOneTimePasswordValue($class, $username, $expires, $password)
    {
        return $this->encodeHash(array(
            $class,
            base64_encode($username),
            $expires,
            $this->generateHash($class, $username, $expires, $password)
        ));
    }
}