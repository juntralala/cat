<?php

namespace App\Dto\Response;

class PaginatedResponse {

    public function __construct(
        public $data,
        public $currentPage,
        public $perPage,
        public $lastPage,
        public $total,
    ){
    }
}