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
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $subdomain;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hostname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $suffix;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $registrabledomain;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $fullhost;

    /**
     * @Gedmo\Versioned
     * @ORM\Column(type="string", length=255)
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getSubdomain(): ?string
    {
        return $this->subdomain;
    }

    public function setSubdomain(string $subdomain): self
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

    public function setSuffix(string $suffix): self
    {
        $this->suffix = $suffix;

        return $this;
    }

    public function getRegistrableDomain(): ?string
    {
        return $this->registrabledomain;
    }

    public function setRegistrableDomain(string $registrabledomain): self
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

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }
}
