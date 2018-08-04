<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @Gedmo\Loggable
 * @ORM\Entity(repositoryClass="App\Repository\WebobjectRepository")
 */
class Webobject
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=20)
     */
    private $class;

    private $subdomain;

    private $hostname;

    private $suffix;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $registrabledomain;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullhost;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ip;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255)
     */
    private $slug;

    public function getId()
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name = null): self
    {
        $this->name = $name;

        return $this;
    }

    public function getClass(): ?string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function setSubdomain(string $subdomain = null): self
    {
        $this->subdomain = $subdomain;

        return $this;
    }

    public function getHostname(): ?string
    {
        return $this->hostname;
    }

    public function setHostname(string $hostname): self
    {
        $this->hostname = $hostname;

        return $this;
    }

    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    public function setSuffix(string $suffix = null): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getRegistrableDomain(): ?string
    {
        return $this->registrabledomain;
    }

    public function setRegistrableDomain(string $registrabledomain = null): self
    {
        $this->registrabledomain = $registrabledomain;

        return $this;
    }

    public function getFullHost(): ?string
    {
        return $this->fullhost;
    }

    public function setFullHost(string $fullhost): self
    {
        $this->fullhost = $fullhost;

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip = null): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
