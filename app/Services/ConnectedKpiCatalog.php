<?php

namespace App\Services;

/**
 * Maps incoming metric keys from spoke tools to KPI definition metadata.
 *
 * Known keys get rich bilingual labels; unknown keys are still accepted and
 * auto-titled, so new tools can start pushing metrics without a code change.
 */
class ConnectedKpiCatalog
{
    /**
     * @var array<string, array<string, mixed>>
     */
    public const MAP = [
        'enterprise_readiness' => [
            'name_de' => 'Enterprise Readiness',
            'name_en' => 'Enterprise Readiness',
            'description_de' => 'Gesamtbewertung der Unternehmensreife (Durchschnitt der 5 Säulen).',
            'description_en' => 'Overall enterprise maturity score (average of the 5 pillars).',
            'category' => 'Strategic',
            'unit' => 'score',
            'direction' => 'higher_better',
        ],
        'audit.umsatz' => [
            'name_de' => 'Umsatz',
            'name_en' => 'Revenue',
            'description_de' => 'Audit-Säule: Zuverlässigkeit und Planbarkeit der Einnahmen.',
            'description_en' => 'Audit pillar: how reliably and predictably income is generated.',
            'category' => 'Financial',
            'unit' => 'score',
            'direction' => 'higher_better',
        ],
        'audit.gewinn' => [
            'name_de' => 'Gewinn',
            'name_en' => 'Profit',
            'description_de' => 'Audit-Säule: gesunde, nachhaltige Gewinnmargen.',
            'description_en' => 'Audit pillar: healthy, sustainable profit margins.',
            'category' => 'Financial',
            'unit' => 'score',
            'direction' => 'higher_better',
        ],
        'audit.ordnung' => [
            'name_de' => 'Ordnung',
            'name_en' => 'Order',
            'description_de' => 'Audit-Säule: Prozesse, Struktur und operative Ordnung.',
            'description_en' => 'Audit pillar: processes, structure and operational order.',
            'category' => 'Operations',
            'unit' => 'score',
            'direction' => 'higher_better',
        ],
        'audit.einfluss' => [
            'name_de' => 'Einfluss',
            'name_en' => 'Influence',
            'description_de' => 'Audit-Säule: Markenautorität, Kundenbindung und Marktposition.',
            'description_en' => 'Audit pillar: brand authority, loyalty and market position.',
            'category' => 'Marketing',
            'unit' => 'score',
            'direction' => 'higher_better',
        ],
        'audit.vermaechtnis' => [
            'name_de' => 'Vermächtnis',
            'name_en' => 'Legacy',
            'description_de' => 'Audit-Säule: langfristige Nachhaltigkeit und Wirkung.',
            'description_en' => 'Audit pillar: long-term sustainability and impact.',
            'category' => 'Strategic',
            'unit' => 'score',
            'direction' => 'higher_better',
        ],
    ];

    /**
     * Resolve KPI definition metadata for a metric key.
     *
     * @return array<string, mixed>
     */
    public static function resolve(string $key, string $source): array
    {
        if (isset(self::MAP[$key])) {
            return self::MAP[$key];
        }

        $label = str($key)->afterLast('.')->headline()->toString();

        return [
            'name_de' => $label,
            'name_en' => $label,
            'description_de' => null,
            'description_en' => null,
            'category' => ucfirst($source),
            'unit' => null,
            'direction' => 'higher_better',
        ];
    }
}
