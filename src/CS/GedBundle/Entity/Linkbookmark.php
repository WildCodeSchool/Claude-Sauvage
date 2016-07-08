<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Linkbookmark
 *
 * @ORM\Table(name="linkbookmark")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\LinkbookmarkRepository")
 */
class Linkbookmark
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
     * @ORM\Column(name="iduser", type="integer")
     */
    private $iduser;

    /**
     * @var int
     *
     * @ORM\Column(name="idfile", type="integer")
     */
    private $idfile;


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
     * Set iduser
     *
     * @param integer $iduser
     * @return Linkbookmark
     */
    public function setIduser($iduser)
    {
        $this->iduser = $iduser;

        return $this;
    }

    /**
     * Get iduser
     *
     * @return integer 
     */
    public function getIduser()
    {
        return $this->iduser;
    }

    /**
     * Set idfile
     *
     * @param integer $idfile
     * @return Linkbookmark
     */
    public function setIdfile($idfile)
    {
        $this->idfile = $idfile;

        return $this;
    }

    /**
     * Get idfile
     *
     * @return integer 
     */
    public function getIdfile()
    {
        return $this->idfile;
    }
}
