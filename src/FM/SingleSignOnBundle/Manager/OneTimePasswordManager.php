<?php

namespace FM\SingleSignOnBundle\Manager;

use FM\SingleSignOnBundle\Entity\OneTimePassword;

class OneTimePasswordManager
{
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    public function create($hash, $expires = null)
    {
        $otp = $this->em->getRepository('FMSingleSignOnBundle:OneTimePassword')->findByHash($hash);
        if (!empty($otp)) {
            throw new \Exception(sprintf('A one-time-password for hash "%s" already exists', $hash));
        }

        // set expires to 5 minutes by default
        if (is_null($expires)) {
            $expires = time() + 300;
        }

        $password = null;

        $i = 0;

        // 20 tries should be more than enough
        while (++$i < 20) {

            $pass = $this->generateRandomValue();

            // make sure it's unique
            if ($this->get($pass)) {
                continue;
            }

            $otp = new OneTimePassword();
            $otp->setHash($hash);
            $otp->setPassword($pass);
            $otp->setUsed(false);
            $otp->setCreated(new \DateTime('@' . $expires));

            $this->em->persist($otp);
            $this->em->flush();

            $password = $pass;

            break;
        }

        if (is_null($password)) {
            throw new \Exception('Could not create a one-time-password');
        }

        return $password;
    }

    public function get($pass)
    {
        return $this->em->getRepository('FMSingleSignOnBundle:OneTimePassword')->findOneByPassword($pass);
    }

    public function isValid(OneTimePassword $otp)
    {
        return $otp->getUsed() === false;
    }

    public function invalidate(OneTimePassword $otp)
    {
        $otp->setUsed(true);
        $this->em->flush();
    }

    protected function generateRandomValue()
    {
        if (!function_exists('openssl_random_pseudo_bytes')) {
            throw new \Exception('Could not produce a cryptographically strong random value. Please install/update the OpenSSL extension.');
        }

        $bytes = openssl_random_pseudo_bytes(64, $strong);

        if (true === $strong && false !== $bytes) {
            return base64_encode($bytes);
        }

        return base64_encode(hash('sha512', uniqid(mt_rand(), true), true));
    }
}