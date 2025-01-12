<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\MotocicletaRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MotocicletaRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource]
class Motocicleta
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "El modelo es obligatorio.")]
    #[Assert\Length(
        max: 50,
        maxMessage: "El modelo no puede exceder los 50 caracteres."
    )]
    private ?string $modelo = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "La cilindrada es obligatoria.")]
    #[Assert\Positive(message: "La cilindrada debe ser un número positivo.")]
    private ?int $cilindrada = null;

    #[ORM\Column(length: 40)]
    #[Assert\NotBlank(message: "La marca es obligatoria.")]
    #[Assert\Length(
        max: 40,
        maxMessage: "La marca no puede exceder los 40 caracteres."
    )]
    private ?string $marca = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "El tipo es obligatorio.")]
    #[Assert\Length(
        max: 255,
        maxMessage: "El tipo no puede exceder los 255 caracteres."
    )]
    private ?string $tipo = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY)]
    #[Assert\NotNull(message: "El campo 'extras' es obligatorio.")]
    #[Assert\Count(
        max: 20,
        maxMessage: "El campo 'extras' no puede contener más de 20 elementos."
    )]
    private array $extras = [];

    #[ORM\Column(nullable: true)]
    #[Assert\PositiveOrZero(message: "El peso debe ser un número positivo o cero.")]
    private ?int $peso = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column]
    #[ApiProperty(
        readable: true,
        writable: true,
        writableOnUpdate: false
    )]
    #[Assert\NotNull(message: "El campo 'edicionLimitada' es obligatorio.")]
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

    #[ORM\PrePersist]
    public function setCreatedAt(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    #[ORM\PreUpdate]
    public function setUpdatedAt(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function isEdicionLimitada(): ?bool
    {
        return $this->edicionLimitada;
    }

    public function setEdicionLimitada(bool $edicionLimitada): static
    {
        if ($this->id !== null) {
            throw new \LogicException("No puedes modificar el campo 'edicionLimitada' una vez creado.");
        }

        $this->edicionLimitada = $edicionLimitada;

        return $this;
    }

}
