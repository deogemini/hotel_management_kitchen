<?php

namespace App\Support;

class AmountInWords
{
    public static function format(string $amount, string $currency): string
    {
        [$whole, $fraction] = explode('.', number_format((float) $amount, 2, '.', ''));

        return strtoupper(self::number((int) $whole).' '.$currency.((int) $fraction ? ' and '.self::number((int) $fraction).' cents' : '').' only');
    }

    private static function number(int $number): string
    {
        $small = ['zero', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'ten', 'eleven', 'twelve', 'thirteen', 'fourteen', 'fifteen', 'sixteen', 'seventeen', 'eighteen', 'nineteen'];
        if ($number < 20) {
            return $small[$number];
        }
        if ($number < 100) {
            return ['', '', 'twenty', 'thirty', 'forty', 'fifty', 'sixty', 'seventy', 'eighty', 'ninety'][intdiv($number, 10)].($number % 10 ? ' '.self::number($number % 10) : '');
        }
        foreach ([1000000000 => 'billion', 1000000 => 'million', 1000 => 'thousand', 100 => 'hundred'] as $scale => $name) {
            if ($number >= $scale) {
                return self::number(intdiv($number, $scale)).' '.$name.($number % $scale ? ' '.self::number($number % $scale) : '');
            }
        }

        return '';
    }
}
