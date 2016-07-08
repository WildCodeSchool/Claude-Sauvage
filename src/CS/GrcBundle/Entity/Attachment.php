<?php

namespace CS\GrcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Attachment
 *
 * @ORM\Table(name="attachment")
 * @ORM\Entity(repositoryClass="CS\GrcBundle\Repository\AttachmentRepository")
 */
class Attachment
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
     * @ORM\Column(name="idticket", type="integer", nullable=true)
     */
    private $idticket;

    /**
     * @var int
     *
     * @ORM\Column(name="idcomment", type="integer", nullable=true)
     */
    private $idcomment;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", length=255)
     */
    private $path;


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
     * Set idticket
     *
     * @param integer $idticket
     * @return Attachment
     */
    public function setIdticket($idticket)
    {
        $this->idticket = $idticket;

        return $this;
    }

    /**
     * Get idticket
     *
     * @return integer 
     */
    public function getIdticket()
    {
        return $this->idticket;
    }

    /**
     * Set idcomment
     *
     * @param integer $idcomment
     * @return Attachment
     */
    public function setIdcomment($idcomment)
    {
        $this->idcomment = $idcomment;

        return $this;
    }

    /**
     * Get idcomment
     *
     * @return integer 
     */
    public function getIdcomment()
    {
        return $this->idcomment;
    }

    /**
     * Set path
     *
     * @param string $path
     * @return Attachment
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
}
