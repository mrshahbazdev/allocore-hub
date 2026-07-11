<?php

namespace Database\Seeders;

use App\Models\Tool;
use Illuminate\Database\Seeder;

class ToolSeeder extends Seeder
{
    public function run(): void
    {
        $tools = [
            [
                'slug' => 'gmbh',
                'name' => 'GmbH Analyse',
                'description' => 'GmbH-Gründung und Unternehmensanalyse.',
                'internal_route' => 'gmbh.index',
                'namespace' => 'App\\Http\\Controllers\\GmbhAnalyseController',
                'icon' => 'building-office',
            ],
            [
                'slug' => 'jahresabschluss',
                'name' => 'Jahresabschluss',
                'description' => 'Jährliche Abschlussanalyse und KPIs.',
                'internal_route' => 'jahresabschluss.index',
                'namespace' => 'App\\Http\\Controllers\\JahresabschlussController',
                'icon' => 'document-text',
            ],
            [
                'slug' => 'immobilien',
                'name' => 'Immobilienanalyse',
                'description' => 'Immobilienbewertung und Renditeanalyse.',
                'internal_route' => 'immobilien.index',
                'namespace' => 'App\\Http\\Controllers\\ImmobilienController',
                'icon' => 'home',
            ],
            [
                'slug' => 'audit',
                'name' => 'Audit',
                'description' => 'Unternehmens- und Prozess-Audits.',
                'internal_route' => 'audit.index',
                'namespace' => 'App\\Modules\\Audit',
                'icon' => 'clipboard-document-check',
            ],
            [
                'slug' => 'invoice',
                'name' => 'Rechnungen',
                'description' => 'Rechnungen, Kunden und Zahlungen verwalten.',
                'internal_route' => 'invoice.index',
                'namespace' => 'App\\Modules\\Invoice',
                'icon' => 'banknotes',
            ],
            [
                'slug' => 'keyword-cluster',
                'name' => 'Keyword Cluster',
                'description' => 'KI-basierte Keyword-Cluster und Content-Planung.',
                'internal_route' => 'keyword-cluster.index',
                'namespace' => 'App\\Modules\\KeywordCluster',
                'icon' => 'globe-alt',
            ],
        ];

        foreach ($tools as $tool) {
            Tool::firstOrCreate(['slug' => $tool['slug']], $tool);
        }
    }
}
