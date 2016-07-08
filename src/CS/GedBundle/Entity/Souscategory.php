<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Souscategory
 *
 * @ORM\Table(name="souscategory")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\SouscategoryRepository")
 */
class Souscategory
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="idcategory", type="integer")
     */
    private $idcategory;


    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Souscategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set idcategory
     *
     * @param integer $idcategory
     * @return Souscategory
     */
    public function setIdcategory($idcategory)
    {
        $this->idcategory = $idcategory;

        return $this;
    }

    /**
     * Get idcategory
     *
     * @return integer 
     */
    public function getIdcategory()
    {
        return $this->idcategory;
    }
}
