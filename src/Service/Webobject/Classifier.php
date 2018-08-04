<?php

namespace App\Service\Webobject;

use Symfony\Component\Validator\Validation;

use Symfony\Component\Validator\Constraints\Ip;

use LayerShifter\TLDExtract\Extract;

class Classifier
{
    private $extract;

    public function __construct()
    {
        $this->extract = New Extract(null, null, Extract::MODE_ALLOW_ICANN);
    }

    public function getClass(string $value)
    {
        $result = $this->extract->parse($value);

        switch(true)
        {
            case $result->isValidDomain() && $result->getFullHost() === $value:

                return "host";
                break;

            case $result->isValidDomain():

                return "url";
                break;

            case ($result->isIp() && $this->isPublicIp($value)):

                return "ip";
                break;

            default:

                return "undefined";

        }
    }

    protected function isPublicIp(string $value)
    {
        $validator = Validation::createValidator();
        $violations = $validator->validate($value, array(
            new Ip(array(
                'version' => '4_public'
            ))
        ));

        return 0 === count($violations) ? true : false;
    }
}