<?php

namespace App\Service\Webobject;

use LayerShifter\TLDExtract\Extract;

use App\Entity\Webobject;

use App\Service\Webobject\Classifier;

class Converter
{
    private $initialValue;

    private $result;

    private $webobject;

    private $classifier;

    public function __construct(string $value)
    {
        $extract = New Extract();

        $this->webobject = New Webobject();
        $this->classifier = New Classifier();

        $this->initialValue = $value;
        $this->result = $extract->parse($value);
    }

    public function getInitialValue()
    {
        return $this->initialValue;
    }

    public function getClass()
    {
        return $this->classifier->getClass($this->getInitialValue());
    }

    public function getWebobject()
    {
        $this->webobject->setClass($this->getClass());
        $this->webobject->setSubdomain($this->result->getSubdomain());
        $this->webobject->setHostname($this->result->getHostname());
        $this->webobject->setSuffix($this->result->getSuffix());
        $this->webobject->setFullHost($this->getClass() !== 'undefined' ? $this->result->getFullHost() : '');
        $this->webobject->setRegistrableDomain(($this->result->getRegistrableDomain()));
        $this->webobject->setIp($this->result->isIp() ? $this->result->getFullhost() : null);

        return $this->webobject;
    }
}