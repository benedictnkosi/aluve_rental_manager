<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * InspectionImage
 *
 * @ORM\Table(name="inspection_image", indexes={@ORM\Index(name="inspection_image_inspection", columns={"inspection"})})
 * @ORM\Entity
 */
class InspectionImage
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var Inspection
     *
     * @ORM\ManyToOne(targetEntity="Inspection")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="inspection", referencedColumnName="id")
     * })
     */
    private $inspection;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Inspection
     */
    public function getInspection(): Inspection
    {
        return $this->inspection;
    }

    /**
     * @param Inspection $inspection
     */
    public function setInspection(Inspection $inspection): void
    {
        $this->inspection = $inspection;
    }


}
