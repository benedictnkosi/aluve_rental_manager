<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Tenant
 *
 * @ORM\Table(name="tenant", indexes={@ORM\Index(name="tenant_unit", columns={"unit"}), @ORM\Index(name="debit_order", columns={"debit_order"})})
 * @ORM\Entity
 */
class Tenant
{
    /**
     * @var int
     *
     * @ORM\Column(name="idtenant", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idtenant;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=45, nullable=true)
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="phone", type="string", length=45, nullable=true)
     */
    private $phone;

    /**
     * @var string|null
     *
     * @ORM\Column(name="email", type="string", length=45, nullable=true)
     */
    private $email;

    /**
     * @var string|null
     *
     * @ORM\Column(name="quickbooks_ref", type="string", length=45, nullable=true)
     */
    private $quickbooksRef;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

    /**
     * @var DebitOrder
     *
     * @ORM\ManyToOne(targetEntity="DebitOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="debit_order", referencedColumnName="iddebit_order")
     * })
     */
    private $debitOrder;

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
     * @return int
     */
    public function getIdtenant(): int
    {
        return $this->idtenant;
    }

    /**
     * @param int $idtenant
     */
    public function setIdtenant(int $idtenant): void
    {
        $this->idtenant = $idtenant;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getPhone(): ?string
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     */
    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string|null
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     */
    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getQuickbooksRef(): ?string
    {
        return $this->quickbooksRef;
    }

    /**
     * @param string|null $quickbooksRef
     */
    public function setQuickbooksRef(?string $quickbooksRef): void
    {
        $this->quickbooksRef = $quickbooksRef;
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
     * @return DebitOrder
     */
    public function getDebitOrder(): DebitOrder
    {
        return $this->debitOrder;
    }

    /**
     * @param DebitOrder $debitOrder
     */
    public function setDebitOrder(DebitOrder $debitOrder): void
    {
        $this->debitOrder = $debitOrder;
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
