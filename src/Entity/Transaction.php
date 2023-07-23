<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Transaction
 *
 * @ORM\Table(name="transaction", indexes={@ORM\Index(name="lease_id", columns={"lease"})})
 * @ORM\Entity
 */
class Transaction
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $date = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=100, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="amount", type="integer", nullable=false)
     */
    private $amount;

    /**
     * @var Leases
     *
     * @ORM\ManyToOne(targetEntity="Leases")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="lease", referencedColumnName="id")
     * })
     */
    private $lease;

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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
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

    /**
     * @var int
     *
     * @ORM\Column(name="email_id", type="integer", nullable=false)
     */
    private $emailId = '0';

    /**
     * @return int
     */
    public function getEmailId(): int|string
    {
        return $this->emailId;
    }

    /**
     * @param int $emailId
     */
    public function setEmailId(int|string $emailId): void
    {
        $this->emailId = $emailId;
    }


}
