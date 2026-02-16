<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons\Tests\Unit;

use Clntdev\BladePaymentIcons\CardMetadata;
use Clntdev\BladePaymentIcons\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CardMetadataTest extends TestCase
{
    protected CardMetadata $metadata;

    protected function setUp(): void
    {
        parent::setUp();

        $this->metadata = app(CardMetadata::class);
    }

    #[Test]
    public function resolve_alias_for_canonical_types(): void
    {
        $resolved = $this->metadata->resolveAlias('Visa');

        $this->assertEquals('Visa', $resolved['type']);
        $this->assertNull($resolved['variant']);

        $resolved = $this->metadata->resolveAlias('Mastercard');

        $this->assertEquals('Mastercard', $resolved['type']);
        $this->assertNull($resolved['variant']);
    }

    #[Test]
    public function resolve_alias_for_amex(): void
    {
        // Amex alias
        $resolved = $this->metadata->resolveAlias('Amex');

        $this->assertEquals('AmericanExpress', $resolved['type']);
        $this->assertNull($resolved['variant']);

        // Full name
        $resolved = $this->metadata->resolveAlias('AmericanExpress');

        $this->assertEquals('AmericanExpress', $resolved['type']);
        $this->assertNull($resolved['variant']);

        // Legacy type
        $resolved = $this->metadata->resolveAlias('Americanexpress');

        $this->assertEquals('AmericanExpress', $resolved['type']);
        $this->assertNull($resolved['variant']);
    }

    #[Test]
    public function resolve_alias_for_variants(): void
    {
        // Hiper variant of Hipercard
        $resolved = $this->metadata->resolveAlias('Hiper');

        $this->assertEquals('Hipercard', $resolved['type']);
        $this->assertEquals('hiper', $resolved['variant']);

        // Code variant of Generic
        $resolved = $this->metadata->resolveAlias('Code');

        $this->assertEquals('Generic', $resolved['type']);
        $this->assertEquals('code', $resolved['variant']);

        // Cvv alias for Code variant
        $resolved = $this->metadata->resolveAlias('Cvv');

        $this->assertEquals('Generic', $resolved['type']);
        $this->assertEquals('code', $resolved['variant']);

        // CodeFront variant
        $resolved = $this->metadata->resolveAlias('CodeFront');

        $this->assertEquals('Generic', $resolved['type']);
        $this->assertEquals('code-front', $resolved['variant']);
    }

    #[Test]
    public function resolve_alias_case_insensitive(): void
    {
        $resolved = $this->metadata->resolveAlias('visa');

        $this->assertEquals('Visa', $resolved['type']);

        $resolved = $this->metadata->resolveAlias('VISA');

        $this->assertEquals('Visa', $resolved['type']);

        $resolved = $this->metadata->resolveAlias('amex');

        $this->assertEquals('AmericanExpress', $resolved['type']);
    }

    #[Test]
    public function resolve_alias_unknown_returns_normalized(): void
    {
        $resolved = $this->metadata->resolveAlias('UnknownCard');

        $this->assertEquals('Unknowncard', $resolved['type']);
        $this->assertNull($resolved['variant']);
    }

    #[Test]
    public function get_card_returns_card_definition(): void
    {
        $card = $this->metadata->getCard('Visa');

        $this->assertNotNull($card);
        $this->assertEquals('Visa', $card['type']);
        $this->assertArrayHasKey('patterns', $card);
        $this->assertArrayHasKey('lengthRange', $card);
    }

    #[Test]
    public function get_card_returns_null_for_unknown(): void
    {
        $card = $this->metadata->getCard('UnknownCard');

        $this->assertNull($card);
    }

    #[Test]
    public function get_card_case_insensitive(): void
    {
        $card = $this->metadata->getCard('visa');

        $this->assertNotNull($card);
        $this->assertEquals('Visa', $card['type']);
    }

    #[Test]
    public function get_all_types_returns_all_card_types(): void
    {
        $types = $this->metadata->getAllTypes();

        $this->assertContains('Visa', $types);
        $this->assertContains('Mastercard', $types);
        $this->assertContains('AmericanExpress', $types);
        $this->assertContains('Discover', $types);
        $this->assertContains('DinersClub', $types);
        $this->assertContains('JCB', $types);
        $this->assertContains('Maestro', $types);
        $this->assertContains('Elo', $types);
        $this->assertContains('Mir', $types);
        $this->assertContains('UnionPay', $types);
        $this->assertContains('Hipercard', $types);
        $this->assertContains('Alipay', $types);
        $this->assertContains('PayPal', $types);
        $this->assertContains('Swish', $types);
        $this->assertContains('Generic', $types);

        $this->assertCount(15, $types);
    }

    #[Test]
    public function get_definitions_sorted_by_specificity(): void
    {
        $definitions = $this->metadata->getDefinitionsSortedBySpecificity();

        // Elo should come before Maestro (more specific patterns)
        $eloIndex = collect($definitions)->search(fn (array $def): bool => $def['type'] === 'Elo');
        $maestroIndex = collect($definitions)->search(fn (array $def): bool => $def['type'] === 'Maestro');

        $this->assertNotNull($eloIndex);
        $this->assertNotNull($maestroIndex);
        $this->assertLessThan($maestroIndex, $eloIndex, 'Elo should be checked before Maestro');
    }

    #[Test]
    public function has_variant(): void
    {
        $this->assertTrue($this->metadata->hasVariant('Hipercard', 'hiper'));
        $this->assertTrue($this->metadata->hasVariant('Generic', 'code'));
        $this->assertTrue($this->metadata->hasVariant('Generic', 'code-front'));

        $this->assertFalse($this->metadata->hasVariant('Visa', 'hiper'));
        $this->assertFalse($this->metadata->hasVariant('Hipercard', 'nonexistent'));
    }

    #[Test]
    public function get_variant_slug(): void
    {
        $this->assertEquals(
            'hiper',
            $this->metadata->getVariantSlug('Hipercard', 'Hiper')
        );
        $this->assertEquals(
            'code',
            $this->metadata->getVariantSlug('Generic', 'Code')
        );
        $this->assertEquals(
            'code-front',
            $this->metadata->getVariantSlug('Generic', 'CodeFront')
        );

        $this->assertNull($this->metadata->getVariantSlug('Visa', 'Hiper'));
        $this->assertNull($this->metadata->getVariantSlug('Hipercard', 'Nonexistent'));
    }
}
