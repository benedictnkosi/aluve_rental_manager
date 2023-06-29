<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Propertyusers
 *
 * @ORM\Table(name="propertyusers", indexes={@ORM\Index(name="property", columns={"property"}), @ORM\Index(name="users", columns={"user"})})
 * @ORM\Entity
 */
class Propertyusers
{
    /**
     * @var int
     *
     * @ORM\Column(name="idPropertyUsers", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idpropertyusers;

    /**
     * @var Properties
     *
     * @ORM\ManyToOne(targetEntity="Properties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="property", referencedColumnName="idProperties")
     * })
     */
    private $property;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user", referencedColumnName="idUsers")
     * })
     */
    private $user;

    /**
     * @return int
     */
    public function getIdpropertyusers(): int
    {
        return $this->idpropertyusers;
    }

    /**
     * @param int $idpropertyusers
     */
    public function setIdpropertyusers(int $idpropertyusers): void
    {
        $this->idpropertyusers = $idpropertyusers;
    }

    /**
     * @return Properties
     */
    public function getProperty(): Properties
    {
        return $this->property;
    }

    /**
     * @param Properties $property
     */
    public function setProperty(Properties $property): void
    {
        $this->property = $property;
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }


}
