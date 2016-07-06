<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gedfiles
 *
 * @ORM\Table(name="gedfiles")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\GedfilesRepository")
 */
class Gedfiles
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
     * @ORM\Column(name="idowner", type="integer")
     */
    private $idowner;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="idcategory", type="integer", nullable=true)
     */
    private $idcategory;

    /**
     * @var int
     *
     * @ORM\Column(name="idsouscategory", type="integer", nullable=true)
     */
    private $idsouscategory;

    /**
     * @var int
     *
     * @ORM\Column(name="idgroup", type="integer", nullable=true)
     */
    private $idgroup;

    /**
     * @var string
     *
     * @ORM\Column(name="originalname", type="string", length=255, nullable=true)
     */
    private $originalname;

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
     * Set idowner
     *
     * @param integer $idowner
     * @return Gedfiles
     */
    public function setIdowner($idowner)
    {
        $this->idowner = $idowner;

        return $this;
    }

    /**
     * Get idowner
     *
     * @return integer 
     */
    public function getIdowner()
    {
        return $this->idowner;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return Gedfiles
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Gedfiles
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string 
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Gedfiles
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set idcategory
     *
     * @param integer $idcategory
     * @return Gedfiles
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
     * Set idsouscategory
     *
     * @param integer $idsouscategory
     * @return Gedfiles
     */
    public function setIdsouscategory($idsouscategory)
    {
        $this->idsouscategory = $idsouscategory;

        return $this;
    }

    /**
     * Get idsouscategory
     *
     * @return integer 
     */
    public function getIdsouscategory()
    {
        return $this->idsouscategory;
    }

    /**
     * Set idgroup
     *
     * @param integer $idgroup
     * @return Gedfiles
     */
    public function setIdgroup($idgroup)
    {
        $this->idgroup = $idgroup;

        return $this;
    }

    /**
     * Get idgroup
     *
     * @return integer 
     */
    public function getIdgroup()
    {
        return $this->idgroup;
    }

    /**
     * Set originalname
     *
     * @param string $originalname
     * @return Gedfiles
     */
    public function setOriginalname($originalname)
    {
        $this->originalname = $originalname;

        return $this;
    }

    /**
     * Get originalname
     *
     * @return string 
     */
    public function getOriginalname()
    {
        return $this->originalname;
    }
}
