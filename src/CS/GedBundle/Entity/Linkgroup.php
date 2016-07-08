<?php

namespace CS\GedBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Linkgroup
 *
 * @ORM\Table(name="linkgroup")
 * @ORM\Entity(repositoryClass="CS\GedBundle\Repository\LinkgroupRepository")
 */
class Linkgroup
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
     * @ORM\Column(name="idgroup", type="integer")
     */
    private $idgroup;


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
     * @return Linkgroup
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
     * Set idgroup
     *
     * @param integer $idgroup
     * @return Linkgroup
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
}
