<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Maintenance
 *
 * @ORM\Table(name="maintenance")
 * @ORM\Entity
 */
class Maintenance
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
     * @ORM\Column(name="uid", type="string", length=36, nullable=false)
     */
    private $uid;

    /**
     * @var int|null
     *
     * @ORM\Column(name="unit", type="integer", nullable=true)
     */
    private $unit;

    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="text", length=65535, nullable=false)
     */
    private $summary;

    /**
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status;

    /**
     * @var int
     *
     * @ORM\Column(name="property", type="integer", nullable=false)
     */
    private $property;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date_logged", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $dateLogged = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_updated", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $lastUpdated = 'CURRENT_TIMESTAMP';

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
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * @param string $uid
     */
    public function setUid(string $uid): void
    {
        $this->uid = $uid;
    }

    /**
     * @return int|null
     */
    public function getUnit(): ?int
    {
        return $this->unit;
    }

    /**
     * @param int|null $unit
     */
    public function setUnit(?int $unit): void
    {
        $this->unit = $unit;
    }

    /**
     * @return string
     */
    public function getSummary(): string
    {
        return $this->summary;
    }

    /**
     * @param string $summary
     */
    public function setSummary(string $summary): void
    {
        $this->summary = $summary;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getProperty(): int
    {
        return $this->property;
    }

    /**
     * @param int $property
     */
    public function setProperty(int $property): void
    {
        $this->property = $property;
    }

    /**
     * @return \DateTime
     */
    public function getDateLogged(): \DateTime|string
    {
        return $this->dateLogged;
    }

    /**
     * @param \DateTime $dateLogged
     */
    public function setDateLogged(\DateTime|string $dateLogged): void
    {
        $this->dateLogged = $dateLogged;
    }

    /**
     * @return \DateTime
     */
    public function getLastUpdated(): \DateTime|string
    {
        return $this->lastUpdated;
    }

    /**
     * @param \DateTime $lastUpdated
     */
    public function setLastUpdated(\DateTime|string $lastUpdated): void
    {
        $this->lastUpdated = $lastUpdated;
    }


}
