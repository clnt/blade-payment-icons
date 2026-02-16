<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons;

class CardUtilities
{
    public function __construct(protected CardMetadata $metadata)
    {
    }

    /**
     * Remove all non-digit characters from a card number.
     */
    public static function sanitizeCardNumber(string $cardNumber): string
    {
        return preg_replace('/\D/', '', $cardNumber);
    }

    /**
     * Validate card number using Luhn algorithm and length check (13-19 digits).
     *
     * Note: Only validates mathematical correctness, not whether the card is active.
     */
    public static function validateCardNumber(string $cardNumber): bool
    {
        $sanitized = self::sanitizeCardNumber($cardNumber);

        if (strlen($sanitized) < 13 || strlen($sanitized) > 19) {
            return false;
        }

        $sum = 0;
        $reversed = strrev($sanitized);
        $length = strlen($reversed);

        for ($index = 0; $index < $length; $index += 1) {
            $digit = (int) $reversed[$index];

            if ($index % 2 === 1) {
                $digit *= 2;

                if ($digit > 9) {
                    $digit = ($digit % 10) + 1;
                }
            }

            $sum += $digit;
        }

        return $sum % 10 === 0;
    }

    /**
     * Detect a card type by testing against regex patterns.
     * Returns canonical type names (AmericanExpress, DinersClub, UnionPay, etc.).
     *
     * For partial card numbers (< 13 digits), tests prefix compatibility.
     * For complete card numbers, validates against full patterns.
     * Returns 'Generic' for unknown or invalid cards.
     */
    public function getCardType(string $cardNumber): string
    {
        $sanitized = self::sanitizeCardNumber($cardNumber);

        if (strlen($sanitized) < 4) {
            return 'Generic';
        }

        $isPartial = strlen($sanitized) < 13;

        foreach ($this->metadata->getDefinitionsSortedBySpecificity() as $card) {
            if ($isPartial) {
                $prefix = $card['patterns']['prefix'] ?? null;

                if ($prefix !== null && preg_match('/' . $prefix . '/', $sanitized)) {
                    return $card['type'];
                }

                continue;
            }

            foreach ($card['patterns']['full'] ?? [] as $pattern) {
                if (preg_match('/' . $pattern . '/', $sanitized)) {
                    return $card['type'];
                }
            }
        }

        return 'Generic';
    }

    /**
     * Format a card number with appropriate spacing for the detected card type.
     */
    public function formatCardNumber(string $cardNumber): string
    {
        $sanitized = self::sanitizeCardNumber($cardNumber);

        if (blank($sanitized)) {
            return '';
        }

        $cardType = $this->getCardType($sanitized);
        $card = $this->metadata->getCard($cardType);

        if ($card === null || $card['formatPattern'] === null) {
            return $sanitized;
        }

        $formatPattern = $card['formatPattern'];

        $pattern = $formatPattern;

        if (is_array($formatPattern) && isset($formatPattern[0]) === false) {
            $lengthKey = (string) strlen($sanitized);
            $pattern = $formatPattern[$lengthKey] ?? collect($formatPattern)->first();
        }

        $formatted = '';
        $position = 0;

        foreach ($pattern as $groupSize) {
            if ($position >= strlen($sanitized)) {
                break;
            }

            if ($formatted !== '') {
                $formatted .= ' ';
            }

            $formatted .= substr($sanitized, $position, $groupSize);
            $position += $groupSize;
        }

        if ($position < strlen($sanitized)) {
            if ($formatted !== '') {
                $formatted .= ' ';
            }

            $formatted .= substr($sanitized, $position);
        }

        return $formatted;
    }

    /**
     * Validate that a card number matches a specific card type and passes Luhn validation.
     */
    public function validateCardForType(string $cardNumber, string $cardType): bool
    {
        $sanitized = self::sanitizeCardNumber($cardNumber);
        $card = $this->metadata->getCard($cardType);

        if ($card === null || blank($card['patterns']['full'])) {
            return false;
        }

        $matchesPattern = collect($card['patterns']['full'])
            ->contains(fn (string $pattern): bool => preg_match('/' . $pattern . '/', $sanitized) === 1);

        return $matchesPattern && self::validateCardNumber($sanitized);
    }

    /**
     * Get the expected length range for a specific card type.
     *
     * @return array{min: int, max: int}|null
     */
    public function getCardLengthRange(string $cardType): ?array
    {
        $card = $this->metadata->getCard($cardType);

        return $card['lengthRange'] ?? null;
    }

    /**
     * Check if a card number is potentially valid based on length requirements.
     */
    public function isCardNumberPotentiallyValid(string $cardNumber): bool
    {
        $sanitized = self::sanitizeCardNumber($cardNumber);
        $cardType = $this->getCardType($sanitized);
        $lengthRange = $this->getCardLengthRange($cardType);

        if ($lengthRange === null) {
            return false;
        }

        return strlen($sanitized) >= $lengthRange['min']
            && strlen($sanitized) <= $lengthRange['max'];
    }

    /**
     * Create a masked version of a card number, showing only the last 4 digits.
     */
    public function maskCardNumber(string $cardNumber, string $maskChar = '*'): string
    {
        $sanitized = self::sanitizeCardNumber($cardNumber);

        if (strlen($sanitized) < 4) {
            return $sanitized;
        }

        $lastFour = substr($sanitized, -4);
        $maskedPortion = str_repeat($maskChar, strlen($sanitized) - 4);

        return $this->formatCardNumber($maskedPortion.$lastFour);
    }

    /**
     * Get a list of card types issued in a specific country/region.
     *
     * @param  array{includeGlobal?: bool}  $options
     * @return array<int, string>
     */
    public function getCardsByCountry(string $countryCode, array $options = []): array
    {
        $includeGlobal = $options['includeGlobal'] ?? true;

        return collect($this->metadata->getCards())
            ->filter(function (array $card) use ($countryCode, $includeGlobal): bool {
                $countries = collect($card['issuingCountries'] ?? []);

                if ($card['issuingCountries'] === null) {
                    return false;
                }

                if ($countryCode === 'GLOBAL') {
                    return $countries->contains('GLOBAL');
                }

                if ($countries->contains($countryCode)) {
                    return true;
                }

                return $includeGlobal && $countries->contains('GLOBAL');
            })
            ->pluck('type')
            ->values()
            ->all();
    }
}
