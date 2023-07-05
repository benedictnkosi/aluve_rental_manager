<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Leases
 *
 * @ORM\Table(name="leases", indexes={@ORM\Index(name="tenant_id", columns={"tenant"}), @ORM\Index(name="unit_id", columns={"unit"})})
 * @ORM\Entity
 */
class Leases
{
    /**
     * @var int
     *
     * @ORM\Column(name="idleases", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idleases;

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
     * @var Units
     *
     * @ORM\ManyToOne(targetEntity="Units")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="unit", referencedColumnName="idunits")
     * })
     */
    private $unit;

    /**
     * @var Tenant
     *
     * @ORM\ManyToOne(targetEntity="Tenant")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="tenant", referencedColumnName="idtenant")
     * })
     */
    private $tenant;

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
     * @ORM\Column(name="deposit_pop", type="string", length=50, nullable=true)
     */
    private $depositPop;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_document", type="string", length=50, nullable=true)
     */
    private $idDocument;

    /**
     * @var string|null
     *
     * @ORM\Column(name="lease_aggreement", type="string", length=50, nullable=true)
     */
    private $leaseAggreement;

    /**
     * @return string|null
     */
    public function getDepositPop(): ?string
    {
        return $this->depositPop;
    }

    /**
     * @param string|null $depositPop
     */
    public function setDepositPop(?string $depositPop): void
    {
        $this->depositPop = $depositPop;
    }

    /**
     * @return string|null
     */
    public function getIdDocument(): ?string
    {
        return $this->idDocument;
    }

    /**
     * @param string|null $idDocument
     */
    public function setIdDocument(?string $idDocument): void
    {
        $this->idDocument = $idDocument;
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
     * @return int
     */
    public function getIdleases(): int
    {
        return $this->idleases;
    }

    /**
     * @param int $idleases
     */
    public function setIdleases(int $idleases): void
    {
        $this->idleases = $idleases;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="deposit", type="integer", nullable=false)
     */
    private $deposit;


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


}
