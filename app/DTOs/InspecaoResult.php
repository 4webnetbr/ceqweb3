<?php

namespace App\DTOs;

class InspecaoResult
{
    public function __construct(
        public bool $temS,
        public bool $temN,
    ) {}
}
