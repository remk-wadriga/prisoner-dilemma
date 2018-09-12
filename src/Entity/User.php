<?php

namespace App\Entity;

use App\Helpers\AccessTokenHelper;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Validator;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Helpers\AccessTokenEntityInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Faker\Factory;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("email", message="User with the same email already registered in system.")
 */
class User implements AccessTokenEntityInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\Email(
     *     message = "The email {{ value }} is not a valid email.",
     * )
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $first_name;

    /**
     * @ORM\Column(type="string", length=100, nullable=true)
     */
    private $last_name;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = ['ROLE_USER'];

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $access_token;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $renew_token;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=32)
     */
    private $salt;

    /**
     * @ORM\Column(type="datetime")
     */
    private $access_token_expired_at;

    /**
     * @Validator\NotEmpty(message="Password can not be blank.", skipEmptyOn="isNotNew")
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Strategy", mappedBy="user")
     */
    private $strategies;

    public function __construct()
    {
        $this->strategies = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->first_name;
    }

    public function setFirstName(string $first_name): self
    {
        $this->first_name = $first_name;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->last_name;
    }

    public function setLastName(string $last_name): self
    {
        $this->last_name = $last_name;

        return $this;
    }

    public function getRoles(): ?array
    {
        return $this->roles;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function setAccessToken(string $access_token): self
    {
        $this->access_token = $access_token;

        return $this;
    }

    public function getRenewToken(): ?string
    {
        return $this->renew_token;
    }

    public function setRenewToken(string $renew_token): self
    {
        $this->renew_token = $renew_token;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getSalt(): ?string
    {
        if ($this->salt === null) {
            $this->salt = Factory::create()->md5;
        }
        return $this->salt;
    }

    public function setSalt(string $salt): self
    {
        $this->salt = $salt;

        return $this;
    }

    public function getAccessTokenExpiredAt(): ?\DateTimeInterface
    {
        return $this->access_token_expired_at;
    }

    public function setAccessTokenExpiredAt(\DateTimeInterface $access_token_expired_at): self
    {
        $this->access_token_expired_at = $access_token_expired_at;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): self
    {
        $this->plainPassword = $password;
        return $this;
    }


    public function getIsNew()
    {
        return $this->id === null;
    }


    // Implementing UserInterface

    public function getUsername()
    {
        return $this->email;
    }

    public function eraseCredentials()
    {

    }

    // Implementing Serializable

    public function serialize()
    {
        return serialize([
            $this->getId(),
            $this->getEmail(),
            $this->getFirstName(),
            $this->getLastName(),
            $this->getPassword(),
            $this->getSalt(),
        ]);
    }

    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->first_name,
            $this->last_name,
            $this->password,
            $this->salt
            ) = unserialize($serialized, ['allowed_classes' => false]);
    }


    // Lifecycle Callbacks

    /**
     * @ORM\PrePersist
     */
    public function beforeCreate()
    {
        if ($this->getAccessToken() === null) {
            $this->setAccessToken(AccessTokenHelper::generateAccessToken($this));
        }
        if ($this->getRenewToken() === null) {
            $this->setRenewToken(AccessTokenHelper::generateAccessToken($this));
        }
        if ($this->getAccessTokenExpiredAt() === null) {
            $this->setAccessTokenExpiredAt(AccessTokenHelper::getAccessTokenExpiredAt());
        }
        if ($this->getCreatedAt() === null) {
            $this->setCreatedAt(new \DateTime());
        }
        if ($this->getUpdatedAt() === null) {
            $this->setUpdatedAt(new \DateTime());
        }
    }

    /**
     * @ORM\PreUpdate
     */
    public function beforeUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @return Collection|Strategy[]
     */
    public function getStrategies(): Collection
    {
        return $this->strategies;
    }

    public function addStrategy(Strategy $strategy): self
    {
        if (!$this->strategies->contains($strategy)) {
            $this->strategies[] = $strategy;
            $strategy->setUser($this);
        }

        return $this;
    }

    public function removeStrategy(Strategy $strategy): self
    {
        if ($this->strategies->contains($strategy)) {
            $this->strategies->removeElement($strategy);
            // set the owning side to null (unless already changed)
            if ($strategy->getUser() === $this) {
                $strategy->setUser(null);
            }
        }

        return $this;
    }


}
