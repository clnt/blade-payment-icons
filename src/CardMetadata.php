<?php

declare(strict_types=1);

namespace Clntdev\BladePaymentIcons;

use Illuminate\Support\Collection;

class CardMetadata
{
    protected Collection $cards;

    protected array $typeAliasMap = [];

    protected array $variantAliasMap = [];

    protected ?array $sortedDefinitions = null;

    public function __construct()
    {
        $this->cards = collect(config('blade-payment-icons.cards', []));

        $this->buildAliasMaps();
    }

    protected function buildAliasMaps(): void
    {
        $this->cards->each(function (array $card): void {
            $type = $card['type'];

            $this->typeAliasMap[strtolower($type)] = $type;

            collect($card['aliases'] ?? [])->each(function (string $alias) use ($type): void {
                $this->typeAliasMap[strtolower($alias)] = $type;
            });

            collect($card['variants'] ?? [])->each(function (array $variantDef, string $variantName) use ($type): void {
                $variantMapping = [
                    'type' => $type,
                    'variant' => $variantDef['slug'],
                ];

                $this->variantAliasMap[strtolower($variantName)] = $variantMapping;

                collect($variantDef['aliases'] ?? [])->each(function (string $variantAlias) use ($variantMapping): void {
                    $this->variantAliasMap[strtolower($variantAlias)] = $variantMapping;
                });
            });
        });
    }

    /**
     * Resolve an input type string to its canonical type and optional variant.
     *
     * @return array{type: string, variant: string|null}
     */
    public function resolveAlias(string $input): array
    {
        $normalizedInput = strtolower(str_replace(['-', '_'], '', $input));

        if (isset($this->variantAliasMap[$normalizedInput])) {
            return [
                'type' => $this->variantAliasMap[$normalizedInput]['type'],
                'variant' => $this->variantAliasMap[$normalizedInput]['variant'],
            ];
        }

        if (isset($this->typeAliasMap[$normalizedInput])) {
            return [
                'type' => $this->typeAliasMap[$normalizedInput],
                'variant' => null,
            ];
        }

        return [
            'type' => ucfirst($normalizedInput),
            'variant' => null,
        ];
    }

    /**
     * Get card definition by type name.
     */
    public function getCard(string $type): ?array
    {
        return $this->cards->first(fn (array $card): bool => strcasecmp($card['type'], $type) === 0);
    }

    /**
     * Get all card definitions sorted by pattern specificity (most specific first).
     *
     * @return array<int, array>
     */
    public function getDefinitionsSortedBySpecificity(): array
    {
        if ($this->sortedDefinitions !== null) {
            return $this->sortedDefinitions;
        }

        $this->sortedDefinitions = $this->cards
            ->map(fn (array $card): array => array_merge($card, [
                'specificity' => $this->calculateSpecificity($card['patterns']['prefix'] ?? null),
            ]))
            ->sortByDesc('specificity')
            ->values()
            ->all();

        return $this->sortedDefinitions;
    }

    /**
     * Calculate pattern specificity for sorting.
     * More specific patterns should be checked first.
     */
    protected function calculateSpecificity(?string $pattern): int
    {
        if ($pattern === null) {
            return 0;
        }

        $specificity = 0;

        preg_match_all('/[0-9]/', $pattern, $exactChars);
        $specificity += count($exactChars[0]) * 10;

        preg_match_all('/\[\d-\d\]/', $pattern, $ranges);
        $specificity += count($ranges[0]) * 5;

        preg_match_all('/\([^)]+\|[^)]+\)/', $pattern, $groups);
        $specificity += count($groups[0]) * 3;

        preg_match_all('/\[0-9\]/', $pattern, $wildcards);
        $specificity -= count($wildcards[0]) * 1;

        if (str_starts_with($pattern, '^')) {
            $specificity += 20;
        }

        return $specificity;
    }

    /**
     * Get all canonical card type names.
     *
     * @return array<int, string>
     */
    public function getAllTypes(): array
    {
        return $this->cards->pluck('type')->all();
    }

    /**
     * Get all card definitions.
     *
     * @return array<int, array>
     */
    public function getCards(): array
    {
        return $this->cards->all();
    }

    /**
     * Check if a variant exists for a given type.
     */
    public function hasVariant(string $type, string $variant): bool
    {
        $card = $this->getCard($type);

        if ($card === null) {
            return false;
        }

        return collect($card['variants'] ?? [])->contains('slug', $variant);
    }

    /**
     * Get the variant slug for a variant name.
     */
    public function getVariantSlug(string $type, string $variantName): ?string
    {
        $card = $this->getCard($type);

        if ($card === null) {
            return null;
        }

        $variant = collect($card['variants'] ?? [])
            ->first(fn (array $variantDef, string $name): bool => strcasecmp($name, $variantName) === 0);

        return $variant['slug'] ?? null;
    }
}
