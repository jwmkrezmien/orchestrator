<?php

namespace App\Service;

use Symfony\Component\Validator\Validation;

use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Url;
use Symfony\Component\Validator\Constraints\Regex;

use LayerShifter\TLDExtract\Extract;

class WebobjectClassifier
{
    private $extract;

    public function __construct()
    {
        $this->extract =  New Extract();
    }

    public function getClass(string $value)
    {
        $result = $this->extract->parse($value);

        switch(true)
        {
            case $result->isValidDomain() && $result->getFullHost() === $value:

                return "domain";
                break;

            case $result->isValidDomain():

                return "url";
                break;

            case $result->isIp():

                return "ip";
                break;

            default:

                return "undefined";

        }
    }


    /*
        public function getClass(string $value)
        {
            switch(true)
            {
                case $this->isUrl($value):

                    return "url";
                    break;

                case $this->isIp($value):

                    return "ip";
                    break;

                case $this->isDomain($value):

                    return "domain";
                    break;

                default:

                    return "undefined";

            }
        }
    /*
        protected function isUrl(string $value)
        {
            $result = $this->extract->parse();
        }

    /*
        protected function isUrl(string $value)
        {
            $validator = Validation::createValidator();
            $violations = $validator->validate($value, array(
                new Url()
            ));

            return 0 === count($violations) ? true : false;
        }

        protected function isIp(string $value)
        {
            $validator = Validation::createValidator();
            $violations = $validator->validate($value, array(
                new Ip(array(
                    'version' => 'all_public'
                ))
            ));

            return 0 === count($violations) ? true : false;
        }

        protected function isDomain(string $value)
        {
            $validator = Validation::createValidator();
            $violations = $validator->validate($value, array(
                new Regex(array(
                    'pattern' => '/^(?!\-)(?:[a-zA-Z\d\-]{0,62}[a-zA-Z\d]\.){1,126}(?!\d+)[a-zA-Z\d]{1,63}$/',
                    'match' => true,
                    'message' => "This is not a valid domain"
                ))
            ));

            return 0 === count($violations) ? true : false;
        }
    */
}