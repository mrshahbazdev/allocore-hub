<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\KpiDefinition;
use App\Models\ToolAccess;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AllocoreIngestTest extends TestCase
{
    use RefreshDatabase;

    private function tool(): ToolAccess
    {
        $company = Company::create(['name' => 'Acme GmbH']);

        return ToolAccess::create([
            'company_id' => $company->id,
            'tool' => 'audit',
            'name' => 'AuditPro',
            'api_key' => ToolAccess::generateKey(),
            'enabled' => true,
            'status' => 'pending',
        ]);
    }

    private function payload(): array
    {
        return [
            'source' => 'audit',
            'external_ref' => 'audit-run-1',
            'recorded_at' => '2026-07-09',
            'metrics' => [
                ['key' => 'enterprise_readiness', 'value' => 3.4, 'scale_max' => 5],
                ['key' => 'audit.umsatz', 'value' => 4, 'scale_max' => 5],
            ],
        ];
    }

    public function test_missing_api_key_is_rejected(): void
    {
        $this->postJson('/api/allocore/kpi/ingest', $this->payload())
            ->assertStatus(401);
    }

    public function test_invalid_api_key_is_rejected(): void
    {
        $this->tool();

        $this->postJson('/api/allocore/kpi/ingest', $this->payload(), [
            'X-Allocore-Api-Key' => 'alc_wrong',
        ])->assertStatus(401);
    }

    public function test_metrics_are_ingested_and_connected_kpis_created(): void
    {
        $tool = $this->tool();

        $this->postJson('/api/allocore/kpi/ingest', $this->payload(), [
            'X-Allocore-Api-Key' => $tool->api_key,
        ])->assertOk()->assertJson(['ok' => true, 'tool' => 'audit']);

        $this->assertDatabaseCount('kpi_definitions', 2 + KpiDefinition::where('is_template', true)->count());
        $this->assertDatabaseHas('kpi_definitions', [
            'company_id' => $tool->company_id,
            'source' => 'audit',
            'source_key' => 'enterprise_readiness',
            'is_connected' => true,
        ]);
    }

    public function test_ingestion_is_idempotent_on_external_ref(): void
    {
        $tool = $this->tool();

        $headers = ['X-Allocore-Api-Key' => $tool->api_key];
        $this->postJson('/api/allocore/kpi/ingest', $this->payload(), $headers)->assertOk();
        $this->postJson('/api/allocore/kpi/ingest', $this->payload(), $headers)->assertOk();

        // Two metrics -> two values, even after re-posting the same run.
        $this->assertDatabaseCount('kpi_values', 2);
    }

    public function test_kpi_visibility_is_scoped_per_user(): void
    {
        $tool = $this->tool();
        $this->postJson('/api/allocore/kpi/ingest', $this->payload(), [
            'X-Allocore-Api-Key' => $tool->api_key,
        ])->assertOk();

        $owner = User::create([
            'company_id' => $tool->company_id,
            'role' => User::ROLE_OWNER,
            'name' => 'Owner',
            'email' => 'owner@acme.test',
            'password' => bcrypt('password'),
        ]);

        $member = User::create([
            'company_id' => $tool->company_id,
            'role' => User::ROLE_MEMBER,
            'name' => 'Member',
            'email' => 'member@acme.test',
            'password' => bcrypt('password'),
        ]);

        $firstKpi = KpiDefinition::where('company_id', $tool->company_id)->first();
        $member->assignedKpis()->sync([$firstKpi->id]);

        $this->assertSame(2, KpiDefinition::visibleTo($owner)->count());
        $this->assertSame(1, KpiDefinition::visibleTo($member)->count());
    }
}
