<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BankAccount
 *
 * @ORM\Table(name="bank_account")
 * @ORM\Entity
 */
class BankAccount
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
     * @ORM\Column(name="bank_name", type="string", length=50, nullable=false)
     */
    private $bankName;

    /**
     * @var string
     *
     * @ORM\Column(name="account_type", type="string", length=11, nullable=false)
     */
    private $accountType;

    /**
     * @var string
     *
     * @ORM\Column(name="account_number", type="string", length=20, nullable=false)
     */
    private $accountNumber;

    /**
     * @var int
     *
     * @ORM\Column(name="branch_code", type="integer", nullable=false)
     */
    private $branchCode;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=11, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

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
     * @return string
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName(string $bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getAccountType(): string
    {
        return $this->accountType;
    }

    /**
     * @param string $accountType
     */
    public function setAccountType(string $accountType): void
    {
        $this->accountType = $accountType;
    }

    /**
     * @return string
     */
    public function getAccountNumber(): string
    {
        return $this->accountNumber;
    }

    /**
     * @param string $accountNumber
     */
    public function setAccountNumber(string $accountNumber): void
    {
        $this->accountNumber = $accountNumber;
    }

    /**
     * @return int
     */
    public function getBranchCode(): int
    {
        return $this->branchCode;
    }

    /**
     * @param int $branchCode
     */
    public function setBranchCode(int $branchCode): void
    {
        $this->branchCode = $branchCode;
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
