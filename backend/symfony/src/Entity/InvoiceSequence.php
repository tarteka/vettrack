<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'invoice_sequences')]
class InvoiceSequence
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    private int $year;

    #[ORM\Column(type: 'integer')]
    private int $lastNumber = 0;

    public function __construct(int $year)
    {
        $this->year = $year;
        $this->lastNumber = 0;
    }

    public function next(): int
    {
        return ++$this->lastNumber;
    }


    public function getYear(): int
    {
        return $this->year;
    }

    public function getLastNumber(): int
    {
        return $this->lastNumber;
    }
}