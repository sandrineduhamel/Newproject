<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ImagesRepository")
 */
class Images
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Galery", inversedBy="images")
     */
    private $galery;

    public function __construct()
    {
        $this->galery = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Galery[]
     */
    public function getGalery(): Collection
    {
        return $this->galery;
    }

    public function addGalery(Galery $galery): self
    {
        if (!$this->galery->contains($galery)) {
            $this->galery[] = $galery;
        }

        return $this;
    }

    public function removeGalery(Galery $galery): self
    {
        if ($this->galery->contains($galery)) {
            $this->galery->removeElement($galery);
        }

        return $this;
    }


}
