<?php

namespace CS\GrcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Grcsouscategory
 *
 * @ORM\Table(name="grcsouscategory")
 * @ORM\Entity(repositoryClass="CS\GrcBundle\Repository\GrcsouscategoryRepository")
 */
class Grcsouscategory
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
     * @var int
     *
     * @ORM\Column(name="idcategory", type="integer")
     */
    private $idcategory;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;


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
     * Set idcategory
     *
     * @param integer $idcategory
     * @return Grcsouscategory
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

    /**
     * Set name
     *
     * @param string $name
     * @return Grcsouscategory
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
}
