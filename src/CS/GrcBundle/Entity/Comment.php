<?php

namespace CS\GrcBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Comment
 *
 * @ORM\Table(name="comment")
 * @ORM\Entity(repositoryClass="CS\GrcBundle\Repository\CommentRepository")
 */
class Comment
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
     * @ORM\Column(name="idticket", type="integer")
     */
    private $idticket;

    /**
     * @var int
     *
     * @ORM\Column(name="idsender", type="integer")
     */
    private $idsender;

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
     * Set idticket
     *
     * @param integer $idticket
     * @return Comment
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
     * Set idsender
     *
     * @param integer $idsender
     * @return Comment
     */
    public function setIdsender($idsender)
    {
        $this->idsender = $idsender;

        return $this;
    }

    /**
     * Get idsender
     *
     * @return integer 
     */
    public function getIdsender()
    {
        return $this->idsender;
    }

    /**
     * Set content
     *
     * @param string $content
     * @return Comment
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
     * @return Comment
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
