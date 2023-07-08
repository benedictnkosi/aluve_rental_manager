<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Document
 *
 * @ORM\Table(name="document", indexes={@ORM\Index(name="tenant", columns={"tenant"}), @ORM\Index(name="document_type", columns={"document_type"})})
 * @ORM\Entity
 */
class Document
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
     * @ORM\Column(name="status", type="string", length=50, nullable=false, options={"default"="active"})
     */
    private $status = 'active';

    /**
     * @var DocumentTypeLookup
     *
     * @ORM\ManyToOne(targetEntity="DocumentTypeLookup")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="document_type", referencedColumnName="id")
     * })
     */
    private $documentType;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;


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
     * @return DocumentTypeLookup
     */
    public function getDocumentType(): DocumentTypeLookup
    {
        return $this->documentType;
    }

    /**
     * @param DocumentTypeLookup $documentType
     */
    public function setDocumentType(DocumentTypeLookup $documentType): void
    {
        $this->documentType = $documentType;
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


}
