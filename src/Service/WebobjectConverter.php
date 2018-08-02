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

                return 'yes';
                break;

            case ($this->result->isValidDomain()):

                return 'no';
                break;

        }
    }
}