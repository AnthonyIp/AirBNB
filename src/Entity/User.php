<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Cocur\Slugify\Slugify;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(
 *  fields={"email"},
 *  message="Un compte a deja été enregistré avec cet email, merci de le modifier"
 * )
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
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank(message="Vous devez renseigner votre prenom")
	 */
	private $firstName;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank(message="Vous devez renseigner votre nom de famille.")
	 */
	private $lastName;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\Email(message="Veuillez renseigner un email valide.")
	 */
	private $email;

	/**
	 * @ORM\Column(type="string", length=255, nullable=true)
	 * @Assert\Url(message="Veuillez donner une URL valide pour votre avatar")
	 */
	private $picture;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank
	 * @Assert\NotCompromisedPassword
	 * @Assert\Regex(pattern="/^(?=.*[A-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).{8,}$/",match=true,message="Sécurité du mot de passe incorrect. Minimum 8 caractères, 1 chiffre, 1 majuscule, 1 caractère spécial")
	 */
	private $hash;

	/**
	 * Confirmation du mot de passe
	 * @Assert\EqualTo(propertyPath="hash",message="Vous n'avez pas correctement confirmé votre mot de passe")
	 */
	private $passwordConfirm;

	/**
	 * @ORM\Column(type="string", length=255)
	 * @Assert\NotBlank
	 * @Assert\Length(min=10, minMessage="Votre introduction doit faire plus de 10 caracteres!")
	 */
	private $introduction;

	/**
	 * @ORM\Column(type="text")
	 * @Assert\NotBlank
	 * @Assert\Length(min=50, minMessage="Votre description detaillée doit faire plus de 50 caracteres!")
	 */
	private $description;

	/**
	 * @ORM\Column(type="string", length=255)
	 */
	private $slug;

	/**
	 * @ORM\OneToMany(targetEntity=Ad::class, mappedBy="author", orphanRemoval=true)
	 */
	private $ads;

	/**
	 * @ORM\ManyToMany(targetEntity=Role::class, mappedBy="users")
	 */
	private $userRoles;

	/**
	 * @ORM\OneToMany(targetEntity=Booking::class, mappedBy="booker")
	 */
	private $bookings;

	/**
	 * @ORM\OneToMany(targetEntity=Comment::class, mappedBy="author", orphanRemoval=true)
	 */
	private $comments;

	/**
	 * Permet de renvoyer le nom complet de l'utilisateur
	 *
	 * @return string
	 */
	public function getFullName(): string
	{
		return "{$this->firstName} {$this->lastName}";
	}

	/**
	 * Permet d'initialiser le slug
	 *
	 * @ORM\PrePersist
	 * @ORM\PreUpdate
	 */
	public function initalizeSlug(): void
	{
		if (empty($this->slug)) {
			$slugify    = new Slugify();
			$this->slug = $slugify->slugify($this->firstName . ' ' . $this->lastName);
		}
	}

	public function __construct()
	{
		$this->ads       = new ArrayCollection();
		$this->userRoles = new ArrayCollection();
		$this->bookings  = new ArrayCollection();
		$this->comments  = new ArrayCollection();
	}

	public function getId(): ?int
	{
		return $this->id;
	}

	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	public function setFirstName(string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	public function setLastName(string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
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

	public function getPicture(): ?string
	{
		return $this->picture;
	}

	public function setPicture(?string $picture): self
	{
		$this->picture = $picture;

		return $this;
	}

	public function getHash(): ?string
	{
		return $this->hash;
	}

	public function setHash(string $hash): self
	{
		$this->hash = $hash;

		return $this;
	}

	public function getIntroduction(): ?string
	{
		return $this->introduction;
	}

	public function setIntroduction(string $introduction): self
	{
		$this->introduction = $introduction;

		return $this;
	}

	public function getDescription(): ?string
	{
		return $this->description;
	}

	public function setDescription(string $description): self
	{
		$this->description = $description;

		return $this;
	}

	public function getSlug(): ?string
	{
		return $this->slug;
	}

	public function setSlug(string $slug): self
	{
		$this->slug = $slug;

		return $this;
	}

	/**
	 * @return Collection|Ad[]
	 */
	public function getAds(): Collection
	{
		return $this->ads;
	}

	public function addAd(Ad $ad): self
	{
		if (!$this->ads->contains($ad)) {
			$this->ads[] = $ad;
			$ad->setAuthor($this);
		}

		return $this;
	}

	public function removeAd(Ad $ad): self
	{
		if ($this->ads->contains($ad)) {
			$this->ads->removeElement($ad);
			// set the owning side to null (unless already changed)
			if ($ad->getAuthor() === $this) {
				$ad->setAuthor(null);
			}
		}

		return $this;
	}

	public function getRoles()
	{
		$roles   = $this->userRoles->map(function ($role) {
			return $role->getTitle();
		})->toArray();
		$roles[] = 'ROLE_USER';
		return $roles;
	}

	public function getPassword()
	{
		return $this->hash;
	}

	public function getSalt()
	{
	}

	public function getUsername()
	{
		return $this->email;
	}

	public function eraseCredentials()
	{
	}

	/**
	 * @return mixed
	 */
	public function getPasswordConfirm()
	{
		return $this->passwordConfirm;
	}

	/**
	 * @param mixed $passwordConfirm
	 */
	public function setPasswordConfirm($passwordConfirm): void
	{
		$this->passwordConfirm = $passwordConfirm;
	}

	/**
	 * @return Collection|Role[]
	 */
	public function getUserRoles(): Collection
	{
		return $this->userRoles;
	}

	public function addUserRole(Role $userRole): self
	{
		if (!$this->userRoles->contains($userRole)) {
			$this->userRoles[] = $userRole;
			$userRole->addUser($this);
		}

		return $this;
	}

	public function removeUserRole(Role $userRole): self
	{
		if ($this->userRoles->contains($userRole)) {
			$this->userRoles->removeElement($userRole);
			$userRole->removeUser($this);
		}

		return $this;
	}

	/**
	 * @return Collection|Booking[]
	 */
	public function getBookings(): Collection
	{
		return $this->bookings;
	}

	public function addBooking(Booking $booking): self
	{
		if (!$this->bookings->contains($booking)) {
			$this->bookings[] = $booking;
			$booking->setBooker($this);
		}

		return $this;
	}

	public function removeBooking(Booking $booking): self
	{
		if ($this->bookings->contains($booking)) {
			$this->bookings->removeElement($booking);
			// set the owning side to null (unless already changed)
			if ($booking->getBooker() === $this) {
				$booking->setBooker(null);
			}
		}

		return $this;
	}

	/**
	 * @return Collection|Comment[]
	 */
	public function getComments(): Collection
	{
		return $this->comments;
	}

	public function addComment(Comment $comment): self
	{
		if (!$this->comments->contains($comment)) {
			$this->comments[] = $comment;
			$comment->setAuthor($this);
		}

		return $this;
	}

	public function removeComment(Comment $comment): self
	{
		if ($this->comments->contains($comment)) {
			$this->comments->removeElement($comment);
			// set the owning side to null (unless already changed)
			if ($comment->getAuthor() === $this) {
				$comment->setAuthor(null);
			}
		}

		return $this;
	}
}
