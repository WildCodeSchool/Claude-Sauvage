<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Gedcom
 *
 * @ORM\Table(name="gedcom")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\GedcomRepository")
 */
class Gedcom
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
     * @ORM\Column(name="iduser", type="integer")
     */
    private $iduser;

    /**
     * @var string
     *
     * @ORM\Column(name="content", type="text", nullable=true)
     */
    private $content;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;


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
     * @return Gedcom
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
     * Set iduser
     *
     * @param integer $iduser
     * @return Gedcom
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
     * Set content
     *
     * @param string $content
     * @return Gedcom
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * Get content
     *
     * @return string 
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return Gedcom
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
}
