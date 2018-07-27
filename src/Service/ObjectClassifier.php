<?php

namespace App\Service;

use Symfony\Component\Validator\Validation;

use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Url;

class ObjectClassifier
{
    public function getClass(string $value, string $version = null)
    {
        switch(true)
        {
            case $this->isUrl($value, $version):

                return "url";
                break;

            case $this->isIp($value):

                return "IP address";
                break;

            default:

                return false;

        }
    }

    protected function isUrl(string $value)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($value, array(
            new Url()
        ));

        return 0 === count($violations) ? true : false;
    }

    protected function isIp(string $value, string $version = '4_public')
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($value, array(
            new Ip(array(
                'version' => $version
            ))
        ));

        return 0 === count($violations) ? true : false;
    }
}