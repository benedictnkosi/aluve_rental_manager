<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Application
 *
 * @ORM\Table(name="application", indexes={@ORM\Index(name="application_unit", columns={"unit"})})
 * @ORM\Entity
 */
class Application
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
     * @ORM\Column(name="name", type="string", length=100, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="phone", type="string", length=10, nullable=false)
     */
    private $phone;

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=100, nullable=false)
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(name="salary", type="integer", nullable=false)
     */
    private $salary;

    /**
     * @var string
     *
     * @ORM\Column(name="occupation", type="string", length=100, nullable=false)
     */
    private $occupation;

    /**
     * @var string|null
     *
     * @ORM\Column(name="bank_statement", type="string", length=100, nullable=true)
     */
    private $bankStatement;

    /**
     * @var string|null
     *
     * @ORM\Column(name="payslip", type="string", length=100, nullable=true)
     */
    private $payslip;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="new"})
     */
    private $status = 'new';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $date = 'CURRENT_TIMESTAMP';

    /**
     * @var string|null
     *
     * @ORM\Column(name="notes", type="text", length=65535, nullable=true)
     */
    private $notes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $updatedDate = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="uid", type="string", length=36, nullable=false)
     */
    private $uid;

    /**
     * @var string
     *
     * @ORM\Column(name="id_number", type="string", length=20, nullable=false)
     */
    private $idNumber;

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
     * @var Properties
     *
     * @ORM\ManyToOne(targetEntity="Properties")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="property", referencedColumnName="idProperties")
     * })
     */
    private $property;

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
     * @ORM\Column(name="co_applicant_bank_statement", type="string", length=50, nullable=true)
     */
    private $coApplicantBankStatement;

    /**
     * @var string|null
     *
     * @ORM\Column(name="co_applicant_payslip", type="string", length=50, nullable=true)
     */
    private $coApplicantPayslip;

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
    public function getCoApplicantBankStatement(): ?string
    {
        return $this->coApplicantBankStatement;
    }

    /**
     * @param string|null $coApplicantBankStatement
     */
    public function setCoApplicantBankStatement(?string $coApplicantBankStatement): void
    {
        $this->coApplicantBankStatement = $coApplicantBankStatement;
    }

    /**
     * @return string|null
     */
    public function getCoApplicantPayslip(): ?string
    {
        return $this->coApplicantPayslip;
    }

    /**
     * @param string|null $coApplicantPayslip
     */
    public function setCoApplicantPayslip(?string $coApplicantPayslip): void
    {
        $this->coApplicantPayslip = $coApplicantPayslip;
    }

    /**
     * @return string|null
     */
    public function getProofOfPayment(): ?string
    {
        return $this->proofOfPayment;
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
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return int
     */
    public function getSalary(): int
    {
        return $this->salary;
    }

    /**
     * @param int $salary
     */
    public function setSalary(int $salary): void
    {
        $this->salary = $salary;
    }

    /**
     * @return string
     */
    public function getOccupation(): string
    {
        return $this->occupation;
    }

    /**
     * @param string $occupation
     */
    public function setOccupation(string $occupation): void
    {
        $this->occupation = $occupation;
    }

    /**
     * @return string|null
     */
    public function getBankStatement(): ?string
    {
        return $this->bankStatement;
    }

    /**
     * @param string|null $bankStatement
     */
    public function setBankStatement(?string $bankStatement): void
    {
        $this->bankStatement = $bankStatement;
    }

    /**
     * @return string|null
     */
    public function getPayslip(): ?string
    {
        return $this->payslip;
    }

    /**
     * @param string|null $payslip
     */
    public function setPayslip(?string $payslip): void
    {
        $this->payslip = $payslip;
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
     * @return string|null
     */
    public function getNotes(): ?string
    {
        return $this->notes;
    }

    /**
     * @param string|null $notes
     */
    public function setNotes(?string $notes): void
    {
        $this->notes = $notes;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedDate(): \DateTime|string
    {
        return $this->updatedDate;
    }

    /**
     * @param \DateTime $updatedDate
     */
    public function setUpdatedDate(\DateTime|string $updatedDate): void
    {
        $this->updatedDate = $updatedDate;
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
     * @return string
     */
    public function getIdNumber(): string
    {
        return $this->idNumber;
    }

    /**
     * @param string $idNumber
     */
    public function setIdNumber(string $idNumber): void
    {
        $this->idNumber = $idNumber;
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
