<?php

namespace App\Service\Webobject;

use Doctrine\ORM\EntityManagerInterface;

use App\Entity\Webobject;
use phpDocumentor\Reflection\Types\Integer;

class Updater
{
    private $em;

    private $storedHosts;

    private $newValidHosts = array();

    public function __construct (EntityManagerInterface $em)
    {
        $this->em = $em;

        $this->getStoredHosts();
    }

    public function getAllWebobjects() : ?array
    {
        return $this->em->getRepository(Webobject::class)->findAll();
    }

    public function addWebobject (Webobject $webobject) : ?bool
    {
        // assess whether this new object is already in the database
        if (in_array($webobject->getFullHost(), $this->storedHosts) || in_array($webobject->getFullHost(), $this->newValidHosts))
        {
            // if a match is found, the webobject is already in the database
            return false;

        }else
        {
            // if not, push the new webobjects into the webobjects array
            $this->em->persist($webobject);

            // add the new full host to the new valid hosts array to check for duplicates in this batch
            array_push($this->newValidHosts, $webobject->getFullHost());
            return true;
        }
    }

    public function updateWebobject(Webobject $webobject)
    {
        $this->em->persist($webobject);
    }

    private function getStoredHosts()
    {
        $this->storedHosts = array_column(
            $this->em->getRepository(Webobject::class)->getStoredHosts(),
            "fullhost"
        );
    }

    public function flush()
    {
        $this->em->flush();
    }
}