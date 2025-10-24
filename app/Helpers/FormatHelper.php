<?php

namespace App\Helpers;

class FormatHelper
{
    /**
     * Formata valor monetário
     */
    public static function money(float $value): string
    {
        return 'R$ ' . number_format($value, 2, ',', '.');
    }

    /**
     * Formata telefone
     */
    public static function phone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);
        
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7, 4);
        }
        
        return $phone;
    }

    /**
     * Formata CPF
     */
    public static function cpf(string $cpf): string
    {
        $cpf = preg_replace('/\D/', '', $cpf);
        
        if (strlen($cpf) === 11) {
            return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9, 2);
        }
        
        return $cpf;
    }

    /**
     * Formata CEP
     */
    public static function zipCode(string $zipCode): string
    {
        $zipCode = preg_replace('/\D/', '', $zipCode);
        
        if (strlen($zipCode) === 8) {
            return substr($zipCode, 0, 5) . '-' . substr($zipCode, 5, 3);
        }
        
        return $zipCode;
    }

    /**
     * Formata data
     */
    public static function date(\DateTime $date, string $format = 'd/m/Y'): string
    {
        return $date->format($format);
    }

    /**
     * Formata data e hora
     */
    public static function dateTime(\DateTime $date, string $format = 'd/m/Y H:i'): string
    {
        return $date->format($format);
    }

    /**
     * Formata peso
     */
    public static function weight(float $weight, string $unit = 'g'): string
    {
        return number_format($weight, 0, ',', '.') . ' ' . $unit;
    }

    /**
     * Formata distância
     */
    public static function distance(float $distance, string $unit = 'km'): string
    {
        return number_format($distance, 1, ',', '.') . ' ' . $unit;
    }
}
