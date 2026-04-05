<?php

declare(strict_types=1);

namespace App\Service;

class UnitConverterService
{
    private const UNICODE_FRACTIONS = [
        '½' => '0.5',
        '⅓' => '0.333',
        '⅔' => '0.667',
        '¼' => '0.25',
        '¾' => '0.75',
        '⅕' => '0.2',
        '⅖' => '0.4',
        '⅗' => '0.6',
        '⅘' => '0.8',
        '⅙' => '0.167',
        '⅚' => '0.833',
        '⅛' => '0.125',
        '⅜' => '0.375',
        '⅝' => '0.625',
        '⅞' => '0.875',
    ];

    private const UNIT_CONVERSIONS = [
        'g'    => ['base' => 'g',  'factor' => 1],
        'kg'   => ['base' => 'g',  'factor' => 1000],
        'ml'   => ['base' => 'ml', 'factor' => 1],
        'l'    => ['base' => 'ml', 'factor' => 1000],
        'L'    => ['base' => 'ml', 'factor' => 1000],
        'tsp'  => ['base' => 'ml', 'factor' => 5],
        'tbsp' => ['base' => 'ml', 'factor' => 15],
        'cup'  => ['base' => 'ml', 'factor' => 250],
        'cups' => ['base' => 'ml', 'factor' => 250],
    ];

    /**
     * @return array{quantity: ?float, unit: ?string}
     */
    public function convert(string $measurement): array
    {
        $normalised = trim($this->normaliseUnicodeFractions($measurement));

        // Match: optional whole/decimal, optional fraction, optional unit
        $pattern = '/^\s*(?:(\d+(?:\.\d+)?)\s*)?(?:(\d+)\/(\d+))?\s*([a-zA-Z]+)?\s*$/';

        if (!preg_match($pattern, $normalised, $matches)) {
            return ['quantity' => null, 'unit' => null];
        }

        return $this->resolveQuantityAndUnit($matches);
    }

    /**
     * @param string[] $matches
     * @return array{quantity: ?float, unit: ?string}
     */
    private function resolveQuantityAndUnit(array $matches): array
    {
        $parsed = $this->parseMatches($matches);

        if ($parsed['whole'] === null && $parsed['numerator'] === null) {
            return ['quantity' => null, 'unit' => null];
        }

        $quantity = $parsed['whole'] ?? 0.0;
        if ($parsed['numerator'] !== null && $parsed['denominator'] !== null && $parsed['denominator'] !== 0) {
            $quantity += $parsed['numerator'] / $parsed['denominator'];
        }

        return $this->applyUnitConversion($quantity, $parsed['unit']);
    }

    /**
     * @param string[] $matches
     * @return array{whole: ?float, numerator: ?int, denominator: ?int, unit: ?string}
     */
    private function parseMatches(array $matches): array
    {
        return [
            'whole'       => ($matches[1] ?? '') !== '' ? (float)$matches[1] : null,
            'numerator'   => ($matches[2] ?? '') !== '' ? (int)$matches[2] : null,
            'denominator' => ($matches[3] ?? '') !== '' ? (int)$matches[3] : null,
            'unit'        => ($matches[4] ?? '') !== '' ? $matches[4] : null,
        ];
    }

    /**
     * @return array{quantity: ?float, unit: ?string}
     */
    private function applyUnitConversion(float $quantity, ?string $unit): array
    {
        if ($unit === null) {
            return ['quantity' => $quantity, 'unit' => null];
        }

        if (!isset(self::UNIT_CONVERSIONS[$unit])) {
            return ['quantity' => null, 'unit' => null];
        }

        $conversion = self::UNIT_CONVERSIONS[$unit];

        return [
            'quantity' => $quantity * $conversion['factor'],
            'unit'     => $conversion['base'],
        ];
    }

    private function normaliseUnicodeFractions(string $measurement): string
    {
        return str_replace(
            array_keys(self::UNICODE_FRACTIONS),
            array_values(self::UNICODE_FRACTIONS),
            $measurement
        );
    }
}
