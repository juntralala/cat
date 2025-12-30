<?php

namespace App\Dto\Response;

class ExpenditurePerSKU {
    public function __construct(
        public string $itemName,
        public string $sku,
        public string $spesificationName,
        public int $count,
        public string $measurementUnit,
        public float $pricePerUnit,
        public float $expenditure
    ) {}
}