<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Inspection
 *
 * @ORM\Table(name="inspection", indexes={@ORM\Index(name="inspection_lease", columns={"lease"})})
 * @ORM\Entity
 */
class Inspection
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
     * @ORM\Column(name="json", type="text", length=65535, nullable=false)
     */
    private $json;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $date = 'CURRENT_TIMESTAMP';

    /**
     * @var string|null
     *
     * @ORM\Column(name="status", type="string", length=11, nullable=true, options={"default"="new"})
     */
    private $status = 'new';


    /**
     * @var Leases
     *
     * @ORM\ManyToOne(targetEntity="Leases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lease", referencedColumnName="idleases")
     * })
     */
    private $lease;

    /**
     * @return string|null
     */
    public function getStatus(): ?string
    {
        return $this->status;
    }

    /**
     * @param string|null $status
     */
    public function setStatus(?string $status): void
    {
        $this->status = $status;
    }

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
    public function getJson(): string
    {
        return $this->json;
    }

    /**
     * @param string $json
     */
    public function setJson(string $json): void
    {
        $this->json = $json;
    }

    /**
     * @return \DateTime
     */
    public function getDate(): \DateTime|string
    {
        return $this->date;
    }

    /**
     * @param \DateTime $date
     */
    public function setDate(\DateTime|string $date): void
    {
        $this->date = $date;
    }

    /**
     * @return Leases
     */
    public function getLease(): Leases
    {
        return $this->lease;
    }

    /**
     * @param Leases $lease
     */
    public function setLease(Leases $lease): void
    {
        $this->lease = $lease;
    }


}
