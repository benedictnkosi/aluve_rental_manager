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
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

    /**
     * @var int|null
     *
     * @ORM\Column(name="adults", type="integer", nullable=true)
     */
    private $adults;

    /**
     * @var int|null
     *
     * @ORM\Column(name="children", type="integer", nullable=true)
     */
    private $children;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_number", type="string", length=20, nullable=true)
     */
    private $idNumber;

    /**
     * @var string|null
     *
     * @ORM\Column(name="id_document_type", type="string", length=20, nullable=true)
     */
    private $idDocumentType;

    /**
     * @var int|null
     *
     * @ORM\Column(name="salary", type="integer", nullable=true)
     */
    private $salary;

    /**
     * @var string|null
     *
     * @ORM\Column(name="occupation", type="string", length=50, nullable=true)
     */
    private $occupation;

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
     * @var DebitOrder
     *
     * @ORM\ManyToOne(targetEntity="DebitOrder")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="debit_order", referencedColumnName="id")
     * })
     */
    private $debitOrder;

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
     * @return int|null
     */
    public function getAdults(): ?int
    {
        return $this->adults;
    }

    /**
     * @param int|null $adults
     */
    public function setAdults(?int $adults): void
    {
        $this->adults = $adults;
    }

    /**
     * @return int|null
     */
    public function getChildren(): ?int
    {
        return $this->children;
    }

    /**
     * @param int|null $children
     */
    public function setChildren(?int $children): void
    {
        $this->children = $children;
    }

    /**
     * @return string|null
     */
    public function getIdNumber(): ?string
    {
        return $this->idNumber;
    }

    /**
     * @param string|null $idNumber
     */
    public function setIdNumber(?string $idNumber): void
    {
        $this->idNumber = $idNumber;
    }

    /**
     * @return string|null
     */
    public function getIdDocumentType(): ?string
    {
        return $this->idDocumentType;
    }

    /**
     * @param string|null $idDocumentType
     */
    public function setIdDocumentType(?string $idDocumentType): void
    {
        $this->idDocumentType = $idDocumentType;
    }

    /**
     * @return int|null
     */
    public function getSalary(): ?int
    {
        return $this->salary;
    }

    /**
     * @param int|null $salary
     */
    public function setSalary(?int $salary): void
    {
        $this->salary = $salary;
    }

    /**
     * @return string|null
     */
    public function getOccupation(): ?string
    {
        return $this->occupation;
    }

    /**
     * @param string|null $occupation
     */
    public function setOccupation(?string $occupation): void
    {
        $this->occupation = $occupation;
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


}
