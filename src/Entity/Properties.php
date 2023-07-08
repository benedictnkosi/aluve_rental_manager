<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Properties
 *
 * @ORM\Table(name="properties")
 * @ORM\Entity
 */
class Properties
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
     * @ORM\Column(name="address", type="string", length=45, nullable=true)
     */
    private $address;

    /**
     * @var int|null
     *
     * @ORM\Column(name="late_fee", type="integer", nullable=true)
     */
    private $lateFee = '0';

    /**
     * @var string|null
     *
     * @ORM\Column(name="lease_file_name", type="string", length=100, nullable=true)
     */
    private $leaseFileName;

    /**
     * @var int|null
     *
     * @ORM\Column(name="rent_due", type="integer", nullable=true, options={"default"="1"})
     */
    private $rentDue = 1;

    /**
     * @var int|null
     *
     * @ORM\Column(name="rent_late_days", type="integer", nullable=true, options={"default"="7"})
     */
    private $rentLateDays = 7;

    /**
     * @var string|null
     *
     * @ORM\Column(name="type", type="string", length=45, nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=20, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

    /**
     * @var string|null
     *
     * @ORM\Column(name="account_number", type="string", length=20, nullable=true)
     */
    private $accountNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="deposit_pecent", type="integer", nullable=false)
     */
    private $depositPecent = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="application_fee", type="integer", nullable=false)
     */
    private $applicationFee = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="guid", type="string", length=36, nullable=false)
     */
    private $guid;

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
    public function getAddress(): ?string
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     */
    public function setAddress(?string $address): void
    {
        $this->address = $address;
    }

    /**
     * @return int|null
     */
    public function getLateFee(): int|string|null
    {
        return $this->lateFee;
    }

    /**
     * @param int|null $lateFee
     */
    public function setLateFee(int|string|null $lateFee): void
    {
        $this->lateFee = $lateFee;
    }

    /**
     * @return string|null
     */
    public function getLeaseFileName(): ?string
    {
        return $this->leaseFileName;
    }

    /**
     * @param string|null $leaseFileName
     */
    public function setLeaseFileName(?string $leaseFileName): void
    {
        $this->leaseFileName = $leaseFileName;
    }

    /**
     * @return int|null
     */
    public function getRentDue(): ?int
    {
        return $this->rentDue;
    }

    /**
     * @param int|null $rentDue
     */
    public function setRentDue(?int $rentDue): void
    {
        $this->rentDue = $rentDue;
    }

    /**
     * @return int|null
     */
    public function getRentLateDays(): ?int
    {
        return $this->rentLateDays;
    }

    /**
     * @param int|null $rentLateDays
     */
    public function setRentLateDays(?int $rentLateDays): void
    {
        $this->rentLateDays = $rentLateDays;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     */
    public function setType(?string $type): void
    {
        $this->type = $type;
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
     * @return string|null
     */
    public function getAccountNumber(): ?string
    {
        return $this->accountNumber;
    }

    /**
     * @param string|null $accountNumber
     */
    public function setAccountNumber(?string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return int
     */
    public function getDepositPecent(): int|string
    {
        return $this->depositPecent;
    }

    /**
     * @param int $depositPecent
     */
    public function setDepositPecent(int|string $depositPecent): void
    {
        $this->depositPecent = $depositPecent;
    }

    /**
     * @return int
     */
    public function getApplicationFee(): int|string
    {
        return $this->applicationFee;
    }

    /**
     * @param int $applicationFee
     */
    public function setApplicationFee(int|string $applicationFee): void
    {
        $this->applicationFee = $applicationFee;
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
