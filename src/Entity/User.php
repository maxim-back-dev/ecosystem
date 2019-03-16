<?php

namespace App\Entity;

use App\Enum\UserRoleEnum;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(name="salt")
     * @var string
     */
    private $salt;

    /**
     * User constructor.
     * @param string $email
     * @param string $username
     * @param string $password
     */
    public function __construct(string $email, string $username, string $password)
    {
        $this->setEmail($email);
        $this->setUsername($username);
        $this->setPassword($password);
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email A user email.
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username User name.
     * @return User
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Returns the roles granted to the user.
     *
     *     public function getRoles()
     *     {
     *         return ['ROLE_USER'];
     *     }
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return array The user roles
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param string $role User role.
     * @return User
     * @see UserRoleEnum
     */
    public function addRole(string $role): self
    {
        $roles   = $this->roles;
        $roles[] = $role;

        $this->roles = array_unique($roles);
        return $this;
    }

    /**
     * @param string $role User role.
     * @return User
     * @see UserRoleEnum
     */
    public function removeRole(string $role): self
    {
        $roles = $this->roles;
        if (($key = array_search($role, $roles)) !== false) {
            unset($roles[$key]);
        }
        $this->roles = array_values($roles);
        return $this;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password User password.
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param string $salt Password salt.
     * @return $this
     */
    public function setSalt(string $salt): self
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
    }
}
