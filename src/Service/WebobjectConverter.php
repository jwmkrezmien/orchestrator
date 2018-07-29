<?php

namespace App\Service;

use LayerShifter\TLDExtract\Extract;

class WebobjectConverter
{
    private $result;

    public function __construct(string $value)
    {
        $extract = New Extract();
        $this->result = $extract->parse($value);
    }

    public function getSubdomain()
    {
        return $this->result->getSubdomain();
    }

    public function getHostname()
    {
        return $this->result->getHostname();
    }

    public function getSuffix()
    {
        return $this->result->getSuffix();
    }

    public function getRegistrableDomain()
    {
        return $this->result->getRegistrableDomain();
    }

    public function getFullHost()
    {
        return $this->result->getFullHost();
    }

    public function getIp()
    {
        switch (true)
        {
            case ($this->result->isIp()):

                return $this->result->getFullHost();
                break;

            case ($this->result->isValidDomain()):

                return gethostbynamel($this->result->getFullHost());
                break;

        }
    }

/*
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
*/
}