<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leases
 *
 * @ORM\Table(name="leases", indexes={@ORM\Index(name="property_id", columns={"property"}), @ORM\Index(name="tenant_id", columns={"tenant"}), @ORM\Index(name="unit_id", columns={"unit"})})
 * @ORM\Entity
 */
class Leases
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
     * @var \DateTime|null
     *
     * @ORM\Column(name="start", type="datetime", nullable=true)
     */
    private $start;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="end", type="datetime", nullable=true)
     */
    private $end;

    /**
     * @var string|null
     *
     * @ORM\Column(name="contract", type="string", length=45, nullable=true)
     */
    private $contract;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

    /**
     * @var int
     *
     * @ORM\Column(name="deposit", type="integer", nullable=false)
     */
    private $deposit;

    /**
     * @var string
     *
     * @ORM\Column(name="guid", type="string", length=36, nullable=false)
     */
    private $guid;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_rules", type="string", length=200, nullable=false)
     */
    private $paymentRules;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lease_aggreement", type="string", length=50, nullable=true)
     */
    private $leaseAggreement;

    /**
     * @var Properties
     *
     * @ORM\ManyToOne(targetEntity="Properties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="property", referencedColumnName="id")
     * })
     */
    private $property;

    /**
     * @var Tenant
     *
     * @ORM\ManyToOne(targetEntity="Tenant")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tenant", referencedColumnName="id")
     * })
     */
    private $tenant;

    /**
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit", referencedColumnName="id")
     * })
     */
    private $unit;

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
     * @return \DateTime|null
     */
    public function getStart(): ?\DateTime
    {
        return $this->start;
    }

    /**
     * @param \DateTime|null $start
     */
    public function setStart(?\DateTime $start): void
    {
        $this->start = $start;
    }

    /**
     * @return \DateTime|null
     */
    public function getEnd(): ?\DateTime
    {
        return $this->end;
    }

    /**
     * @param \DateTime|null $end
     */
    public function setEnd(?\DateTime $end): void
    {
        $this->end = $end;
    }

    /**
     * @return string|null
     */
    public function getContract(): ?string
    {
        return $this->contract;
    }

    /**
     * @param string|null $contract
     */
    public function setContract(?string $contract): void
    {
        $this->contract = $contract;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getDeposit(): int
    {
        return $this->deposit;
    }

    /**
     * @param int $deposit
     */
    public function setDeposit(int $deposit): void
    {
        $this->deposit = $deposit;
    }

    /**
     * @return string
     */
    public function getGuid(): string
    {
        return $this->guid;
    }

    /**
     * @param string $guid
     */
    public function setGuid(string $guid): void
    {
        $this->guid = $guid;
    }

    /**
     * @return string
     */
    public function getPaymentRules(): string
    {
        return $this->paymentRules;
    }

    /**
     * @param string $paymentRules
     */
    public function setPaymentRules(string $paymentRules): void
    {
        $this->paymentRules = $paymentRules;
    }

    /**
     * @return string|null
     */
    public function getLeaseAggreement(): ?string
    {
        return $this->leaseAggreement;
    }

    /**
     * @param string|null $leaseAggreement
     */
    public function setLeaseAggreement(?string $leaseAggreement): void
    {
        $this->leaseAggreement = $leaseAggreement;
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
     * @return Tenant
     */
    public function getTenant(): Tenant
    {
        return $this->tenant;
    }

    /**
     * @param Tenant $tenant
     */
    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    /**
     * @return Units
     */
    public function getUnit(): Units
    {
        return $this->unit;
    }

    /**
     * @param Units $unit
     */
    public function setUnit(Units $unit): void
    {
        $this->unit = $unit;
    }


}
