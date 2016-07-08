<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Linktag
 *
 * @ORM\Table(name="linktag")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\LinktagRepository")
 */
class Linktag
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
     * @ORM\Column(name="idfile", type="integer")
     */
    private $idfile;

    /**
     * @var int
     *
     * @ORM\Column(name="idtag", type="integer")
     */
    private $idtag;


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
     * Set idfile
     *
     * @param integer $idfile
     * @return Linktag
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

    /**
     * Set idtag
     *
     * @param integer $idtag
     * @return Linktag
     */
    public function setIdtag($idtag)
    {
        $this->idtag = $idtag;

        return $this;
    }

    /**
     * Get idtag
     *
     * @return integer 
     */
    public function getIdtag()
    {
        return $this->idtag;
    }
}
