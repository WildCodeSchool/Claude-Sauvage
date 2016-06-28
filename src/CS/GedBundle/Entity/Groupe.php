<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Groupe
 *
 * @ORM\Table(name="groupe")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\GroupeRepository")
 */
class Groupe
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
     * @ORM\Column(name="idcreator", type="integer", nullable=true)
     */
    private $idcreator;


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
     * @return Groupe
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
     * Set idcreator
     *
     * @param integer $idcreator
     * @return Groupe
     */
    public function setIdcreator($idcreator)
    {
        $this->idcreator = $idcreator;

        return $this;
    }

    /**
     * Get idcreator
     *
     * @return integer 
     */
    public function getIdcreator()
    {
        return $this->idcreator;
    }
}
