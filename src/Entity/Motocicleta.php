<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MotocicletaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MotocicletaRepository::class)]
#[ApiResource]
class Motocicleta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $modelo = null;

    #[ORM\Column]
    private ?int $cilindrada = null;

    #[ORM\Column(length: 40)]
    private ?string $marca = null;

    #[ORM\Column(length: 255)]
    private ?string $tipo = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    private array $extras = [];

    #[ORM\Column(nullable: true)]
    private ?int $peso = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    private ?bool $edicionLimitada = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getModelo(): ?string
    {
        return $this->modelo;
    }

    public function setModelo(string $modelo): static
    {
        $this->modelo = $modelo;

        return $this;
    }

    public function getCilindrada(): ?int
    {
        return $this->cilindrada;
    }

    public function setCilindrada(int $cilindrada): static
    {
        $this->cilindrada = $cilindrada;

        return $this;
    }

    public function getMarca(): ?string
    {
        return $this->marca;
    }

    public function setMarca(string $marca): static
    {
        $this->marca = $marca;

        return $this;
    }

    public function getTipo(): ?string
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo): static
    {
        $this->tipo = $tipo;

        return $this;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function setExtras(array $extras): static
    {
        $this->extras = $extras;

        return $this;
    }

    public function getPeso(): ?int
    {
        return $this->peso;
    }

    public function setPeso(?int $peso): static
    {
        $this->peso = $peso;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function isEdicionLimitada(): ?bool
    {
        return $this->edicionLimitada;
    }

    public function setEdicionLimitada(bool $edicionLimitada): static
    {
        $this->edicionLimitada = $edicionLimitada;

        return $this;
    }
}
