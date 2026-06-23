<?php

namespace App\Support;

class Cpf
{
    public static function isValid(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        for ($checkDigitPosition = 9; $checkDigitPosition <= 10; $checkDigitPosition++) {
            if ((int) $cpf[$checkDigitPosition] !== self::checkDigit($cpf, $checkDigitPosition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Gera um CPF aleatorio com digitos verificadores validos (apenas para seeds/testes).
     */
    public static function generate(): string
    {
        $base = '';

        for ($i = 0; $i < 9; $i++) {
            $base .= random_int(0, 9);
        }

        $base .= self::checkDigit($base, 9);
        $base .= self::checkDigit($base, 10);

        return $base;
    }

    private static function checkDigit(string $cpf, int $checkDigitPosition): int
    {
        $sum = 0;

        for ($i = 0; $i < $checkDigitPosition; $i++) {
            $sum += (int) $cpf[$i] * (($checkDigitPosition + 1) - $i);
        }

        $remainder = $sum % 11;

        return $remainder < 2 ? 0 : 11 - $remainder;
    }
}
