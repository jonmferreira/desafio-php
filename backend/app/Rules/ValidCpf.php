<?php

namespace App\Rules;

use App\Support\Cpf;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! Cpf::isValid((string) $value)) {
            $fail('O campo :attribute não é um CPF válido.');
        }
    }
}
