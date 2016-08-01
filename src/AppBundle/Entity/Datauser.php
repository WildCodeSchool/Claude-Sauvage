<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Datauser
 *
 * @ORM\Table(name="datauser")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DatauserRepository")
 */
class Datauser
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
     * @var string
     *
     * @ORM\Column(name="surname", type="string", length=255, nullable=true)
     */
    private $surname;

    /**
     * @var string
     *
     * @ORM\Column(name="firstname", type="string", length=255, nullable=true)
     */
    private $firstname;

    /**
     * @var string
     *
     * @ORM\Column(name="profileimage", type="string", length=255, nullable=true)
     */
    private $profileimage;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="text", nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="tel1", type="text", nullable=true)
     */
    private $tel1;

    /**
     * @var string
     *
     * @ORM\Column(name="tel2", type="text", nullable=true)
     */
    private $tel2;


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
     * @return Datauser
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
     * Set surname
     *
     * @param string $surname
     * @return Datauser
     */
    public function setSurname($surname)
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * Get surname
     *
     * @return string 
     */
    public function getSurname()
    {
        return $this->surname;
    }

    /**
     * Set firstname
     *
     * @param string $firstname
     * @return Datauser
     */
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get firstname
     *
     * @return string 
     */
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set profileimage
     *
     * @param string $profileimage
     * @return Datauser
     */
    public function setProfileimage($profileimage)
    {
        $this->profileimage = $profileimage;

        return $this;
    }

    /**
     * Get profileimage
     *
     * @return string 
     */
    public function getProfileimage()
    {
        return $this->profileimage;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Datauser
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set tel1
     *
     * @param string $tel1
     * @return Datauser
     */
    public function setTel1($tel1)
    {
        $this->title = $tel1;

        return $this;
    }

    /**
     * Get tel1
     *
     * @return string 
     */
    public function getTel1()
    {
        return $this->tel1;
    }

    /**
     * Set tel2
     *
     * @param string $tel2
     * @return Datauser
     */
    public function setTel2($tel2)
    {
        $this->title = $tel2;

        return $this;
    }

    /**
     * Get tel2
     *
     * @return string 
     */
    public function getTel2()
    {
        return $this->tel2;
    }
}
