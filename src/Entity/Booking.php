<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\BookingRepository;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass=BookingRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Booking
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $booker;

    /**
     * @ORM\ManyToOne(targetEntity=Ad::class, inversedBy="bookings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $ad;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan("today", message="La date d'arrivée doit être ultérieure à la date d'aujourd'hui !", groups={"front"})
     * groups={"Front"})
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime")
     * @Assert\GreaterThan(propertyPath="startDate", message="La date de départ doit être plus éloignée que la date d'arrivée !")
     */
    private $endDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $comment;

    /**
     * Creation de la date de creation et met a jour la propriete du prix finale du sejour
     * Callback appele a chaque fois qu'on cree une reservation
     * @ORM\PrePersist
     * @return void
     */
    public function prePersist(): void
    {
        if (empty($this->createdAt)) {
            $this->createdAt = new \DateTime('now', new \DateTimeZone("Europe/Paris"));
        }
        if (empty($this->amount)) {
            /* prix de l'annonce * nombre de jour du sejour*/
            $this->amount = $this->ad->getPrice() * $this->getDuration();
        }
    }

    public function isBookableDates(): bool
    {
        /*Connaitre les dates qui sont impossibles pour l'annonce*/
        $notAvailableDays = $this->ad->getNotAvailableDays();

        /*Comparer les dates choisies avec les dates impossibles*/
        $bookingDays = $this->getDays();

        $formatDay = function ($day) {
            return $day->format('Y-m-d');
        };

        /*Tableau des chaines de caracteres de mes journées*/
        $days = array_map($formatDay, $bookingDays);

        /*Tableau des chaines de caracteres des journées non disponible*/
        $notAvailableDays = array_map($formatDay, $notAvailableDays);

        foreach ($days as $day) {
            if (in_array($day, $notAvailableDays, true)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Permet de récuperer un tableau des journées qui correspondent a ma reservation
     *
     * @return array Un tableau d'objet Datetime representant les jours de la reservation
     */
    public function getDays()
    {

        $resultat = range(
            $this->getStartDate()->getTimestamp(),
            $this->getEndDate()->getTimestamp(),
            24 * 60 * 60
        );

        $days = array_map(function ($daysTimestamp) {
            return new \DateTime(date('Y-m-d', $daysTimestamp));
        }, $resultat);

        return $days;
    }

    /**
     * Calcule le nombre de jours du sejour
     * */
    public function getDuration()
    {
        $diff = $this->endDate->diff($this->startDate);
        return $diff->days;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooker(): ?User
    {
        return $this->booker;
    }

    public function setBooker(?User $booker): self
    {
        $this->booker = $booker;

        return $this;
    }

    public function getAd(): ?Ad
    {
        return $this->ad;
    }

    public function setAd(?Ad $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }
}
