<?php

final readonly class Property
{
    public function __construct(
        public DateTimeImmutable $date,
        public string $address,
        public string $county,
        public ?string $eircode,
        public int $price,
        public string $type,
    ) {}
}