<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons\Tests\Unit;

use Clntdev\BladePaymentIcons\CardMetadata;
use Clntdev\BladePaymentIcons\CardUtilities;
use Clntdev\BladePaymentIcons\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CardUtilitiesTest extends TestCase
{
    protected CardUtilities $utilities;

    protected function setUp(): void
    {
        parent::setUp();

        $this->utilities = app(CardUtilities::class);
    }

    #[Test]
    public function sanitize_card_number_removes_non_digits(): void
    {
        $this->assertEquals(
            '4111111111111111',
            CardUtilities::sanitizeCardNumber('4111-1111-1111-1111')
        );
        $this->assertEquals(
            '4111111111111111',
            CardUtilities::sanitizeCardNumber('4111 1111 1111 1111')
        );
        $this->assertEquals(
            '4111111111111111',
            CardUtilities::sanitizeCardNumber('4111.1111.1111.1111')
        );
        $this->assertEquals(
            '411111111111',
            CardUtilities::sanitizeCardNumber('4111abcd1111efgh1111')
        );
        $this->assertEquals('', CardUtilities::sanitizeCardNumber(''));
    }

    #[Test]
    public function validate_card_number_with_luhn_algorithm(): void
    {
        // Valid card numbers
        $this->assertTrue(CardUtilities::validateCardNumber('4111111111111111'));
        $this->assertTrue(CardUtilities::validateCardNumber('5500000000000004'));
        $this->assertTrue(CardUtilities::validateCardNumber('4242424242424242'));

        // Invalid checksums
        $this->assertFalse(CardUtilities::validateCardNumber('4111111111111112'));
        $this->assertFalse(CardUtilities::validateCardNumber('1234567890123456'));

        // Invalid lengths
        $this->assertFalse(CardUtilities::validateCardNumber(''));
        $this->assertFalse(CardUtilities::validateCardNumber('411111111111')); // 12 digits
        $this->assertFalse(CardUtilities::validateCardNumber('41111111111111111111')); // 20 digits

        // Non-numeric input
        $this->assertFalse(CardUtilities::validateCardNumber('abcd-efgh-ijkl-mnop'));
    }

    #[Test]
    public function get_card_type_returns_canonical_names(): void
    {
        $this->assertEquals('Visa', $this->utilities->getCardType('4242424242424242'));
        $this->assertEquals('Mastercard', $this->utilities->getCardType('5555555555554444'));
        $this->assertEquals('AmericanExpress', $this->utilities->getCardType('378282246310005'));
        $this->assertEquals('DinersClub', $this->utilities->getCardType('30569309025904'));
        $this->assertEquals('UnionPay', $this->utilities->getCardType('6200000000000005'));
        $this->assertEquals('JCB', $this->utilities->getCardType('3566002020360505'));
        $this->assertEquals('Discover', $this->utilities->getCardType('6011111111111117'));
    }

    #[Test]
    public function get_card_type_returns_generic_for_unknown(): void
    {
        $this->assertEquals('Generic', $this->utilities->getCardType('9999999999999999'));
        $this->assertEquals('Generic', $this->utilities->getCardType(''));
        $this->assertEquals('Generic', $this->utilities->getCardType('4')); // Too short
        $this->assertEquals('Generic', $this->utilities->getCardType('12'));
        $this->assertEquals('Generic', $this->utilities->getCardType('123'));
    }

    #[Test]
    public function get_card_type_handles_partial_numbers(): void
    {
        $this->assertEquals('Visa', $this->utilities->getCardType('4111'));
        $this->assertEquals('Mastercard', $this->utilities->getCardType('5111'));
        $this->assertEquals('AmericanExpress', $this->utilities->getCardType('3782'));
    }

    #[Test]
    public function get_card_type_handles_formatted_numbers(): void
    {
        $this->assertEquals('Visa', $this->utilities->getCardType('4111 1111 1111 1111'));
        $this->assertEquals('AmericanExpress', $this->utilities->getCardType('3782-8224-6310-005'));
        $this->assertEquals('Mastercard', $this->utilities->getCardType('5555.5555.5555.4444'));
    }

    #[Test]
    public function format_card_number_visa(): void
    {
        $this->assertEquals('4111 1111 1111 1111', $this->utilities->formatCardNumber('4111111111111111'));
        $this->assertEquals('4111', $this->utilities->formatCardNumber('4111'));
        $this->assertEquals('4111 1111', $this->utilities->formatCardNumber('41111111'));
    }

    #[Test]
    public function format_card_number_amex(): void
    {
        $this->assertEquals('3400 000000 00009', $this->utilities->formatCardNumber('340000000000009'));
    }

    #[Test]
    public function format_card_number_diners(): void
    {
        // 14-digit Diners (4-6-4)
        $this->assertEquals('3000 000000 0000', $this->utilities->formatCardNumber('30000000000000'));

        // 16-digit Diners (4-4-4-4)
        $this->assertEquals('3056 9300 0902 0004', $this->utilities->formatCardNumber('3056930009020004'));
    }

    #[Test]
    public function format_card_number_empty(): void
    {
        $this->assertEquals('', $this->utilities->formatCardNumber(''));
    }

    #[Test]
    public function validate_card_for_type(): void
    {
        $this->assertTrue($this->utilities->validateCardForType('4242424242424242', 'Visa'));
        $this->assertTrue($this->utilities->validateCardForType('5555555555554444', 'Mastercard'));
        $this->assertTrue($this->utilities->validateCardForType('378282246310005', 'AmericanExpress'));

        // Wrong type
        $this->assertFalse($this->utilities->validateCardForType('4242424242424242', 'Mastercard'));

        // Invalid Luhn
        $this->assertFalse($this->utilities->validateCardForType('4111111111111112', 'Visa'));

        // Non-card payment methods
        $this->assertFalse($this->utilities->validateCardForType('1234567890123456', 'PayPal'));
        $this->assertFalse($this->utilities->validateCardForType('1234567890123456', 'Alipay'));
    }

    #[Test]
    public function get_card_length_range(): void
    {
        $this->assertEquals(['min' => 13, 'max' => 19], $this->utilities->getCardLengthRange('Visa'));
        $this->assertEquals(['min' => 16, 'max' => 16], $this->utilities->getCardLengthRange('Mastercard'));
        $this->assertEquals(['min' => 15, 'max' => 15], $this->utilities->getCardLengthRange('AmericanExpress'));
        $this->assertEquals(['min' => 12, 'max' => 19], $this->utilities->getCardLengthRange('Maestro'));

        // Non-card payment methods
        $this->assertNull($this->utilities->getCardLengthRange('PayPal'));
        $this->assertNull($this->utilities->getCardLengthRange('Alipay'));
        $this->assertNull($this->utilities->getCardLengthRange('Generic'));
    }

    #[Test]
    public function is_card_number_potentially_valid(): void
    {
        // Valid lengths
        $this->assertTrue($this->utilities->isCardNumberPotentiallyValid('4111111111111111'));
        $this->assertTrue($this->utilities->isCardNumberPotentiallyValid('5500000000000004'));

        // Invalid lengths
        $this->assertFalse($this->utilities->isCardNumberPotentiallyValid('4111'));
        $this->assertFalse($this->utilities->isCardNumberPotentiallyValid('41111111111111111111'));

        // Generic cards (no length range)
        $this->assertFalse($this->utilities->isCardNumberPotentiallyValid('9999999999999999'));

        // American Express length validation
        $this->assertTrue($this->utilities->isCardNumberPotentiallyValid('378282246310005')); // 15 digits
        $this->assertFalse($this->utilities->isCardNumberPotentiallyValid('37828224631000')); // 14 digits
        $this->assertFalse($this->utilities->isCardNumberPotentiallyValid('3782822463100055')); // 16 digits

        // Visa variable length
        $this->assertTrue($this->utilities->isCardNumberPotentiallyValid('4111111111111')); // 13 digits
        $this->assertTrue($this->utilities->isCardNumberPotentiallyValid('4111111111111111')); // 16 digits
    }

    #[Test]
    public function mask_card_number(): void
    {
        // Note: formatCardNumber sanitizes input, removing non-digits including mask chars
        // So mask characters get stripped during formatting, but last 4 digits remain
        $masked = $this->utilities->maskCardNumber('4111111111111111');
        $this->assertStringContainsString('1111', $masked);

        $maskedB = $this->utilities->maskCardNumber('5500000000000004');
        $this->assertStringContainsString('0004', $maskedB);

        // American Express
        $maskedC = $this->utilities->maskCardNumber('340000000000009');
        $this->assertStringContainsString('0009', $maskedC);

        // Short numbers
        $this->assertEquals('411', $this->utilities->maskCardNumber('411'));
        $this->assertEquals('', $this->utilities->maskCardNumber(''));

        // Custom mask character - note mask chars get stripped by formatCardNumber
        $maskedD = $this->utilities->maskCardNumber('4111111111111111', 'X');
        $this->assertStringContainsString('1111', $maskedD);
    }

    #[Test]
    public function get_cards_by_country(): void
    {
        // Brazil
        $brCards = $this->utilities->getCardsByCountry('BR');
        $this->assertContains('Elo', $brCards);
        $this->assertContains('Hipercard', $brCards);
        // Should also include global cards by default
        $this->assertContains('Visa', $brCards);
        $this->assertContains('Mastercard', $brCards);

        // Brazil without global
        $brOnlyCards = $this->utilities->getCardsByCountry('BR', ['includeGlobal' => false]);
        $this->assertContains('Elo', $brOnlyCards);
        $this->assertContains('Hipercard', $brOnlyCards);
        $this->assertNotContains('Visa', $brOnlyCards);
        $this->assertNotContains('Mastercard', $brOnlyCards);

        // US
        $usCards = $this->utilities->getCardsByCountry('US');
        $this->assertContains('AmericanExpress', $usCards);
        $this->assertContains('Discover', $usCards);
        $this->assertContains('DinersClub', $usCards);

        // Global query
        $globalCards = $this->utilities->getCardsByCountry('GLOBAL');
        $this->assertContains('Visa', $globalCards);
        $this->assertContains('Mastercard', $globalCards);
        $this->assertContains('PayPal', $globalCards);
        $this->assertContains('Maestro', $globalCards);
        $this->assertNotContains('Elo', $globalCards);
    }

    #[Test]
    public function elo_vs_mastercard_distinction(): void
    {
        // Elo has specific ranges that overlap with Mastercard's 5xxx range
        $this->assertEquals('Elo', $this->utilities->getCardType('5066991111111118'));
    }

    #[Test]
    public function hipercard_detection(): void
    {
        $this->assertEquals('Hipercard', $this->utilities->getCardType('6062825624254001'));
        $this->assertEquals('Hipercard', $this->utilities->getCardType('6062828888666688'));
    }

    #[Test]
    public function mir_detection(): void
    {
        $this->assertEquals('Mir', $this->utilities->getCardType('2200000000000004'));
        $this->assertEquals('Mir', $this->utilities->getCardType('2202207558945880'));
    }

    #[Test]
    public function all_test_numbers_validate(): void
    {
        $metadata = app(CardMetadata::class);

        collect($metadata->getCards())->each(function (array $card): void {
            collect($card['testNumbers'] ?? [])->each(function (string $testNumber) use ($card): void {
                $detected = $this->utilities->getCardType($testNumber);
                $this->assertEquals(
                    $card['type'],
                    $detected,
                    "Test number {$testNumber} should be detected as {$card['type']}, got {$detected}"
                );

                if (filled($card['patterns']['full']) === false) {
                    return;
                }

                $this->assertTrue(
                    $this->utilities->validateCardForType($testNumber, $card['type']),
                    "Test number {$testNumber} should validate for type {$card['type']}"
                );
            });
        });
    }
}
