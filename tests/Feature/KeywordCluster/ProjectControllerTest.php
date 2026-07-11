<?php

namespace Tests\Feature\KeywordCluster;

use App\Jobs\GenerateKeywordProjectJob;
use App\Models\Company;
use App\Models\KeywordProject;
use App\Models\Tool;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    private function setupUserWithTool(): array
    {
        $user = User::factory()->create();
        $tool = Tool::firstOrCreate(
            ['slug' => 'keyword-cluster'],
            [
                'name' => 'Keyword Cluster',
                'description' => 'AI keyword cluster and content planning.',
                'internal_route' => 'keyword-cluster.index',
                'namespace' => 'App\\Modules\\KeywordCluster',
                'icon' => 'globe-alt',
            ]
        );

        $company = Company::create([
            'user_id' => $user->id,
            'name' => 'Test Company',
            'slug' => Str::slug('Test Company').'-'.Str::random(6),
        ]);

        $company->users()->attach($user->id, [
            'role' => Company::ROLE_OWNER,
            'is_default' => true,
        ]);

        $company->tools()->attach($tool->id, ['status' => 'active']);

        $user->setCurrentCompany($company);

        return [$user, $company];
    }

    public function test_index_requires_authentication(): void
    {
        $this->get('/keyword-cluster')->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_empty_index(): void
    {
        [$user] = $this->setupUserWithTool();

        $this->actingAs($user)
            ->get('/keyword-cluster')
            ->assertOk()
            ->assertViewIs('keyword-cluster.index');
    }

    public function test_user_can_create_project_and_dispatch_job(): void
    {
        Bus::fake();
        [$user] = $this->setupUserWithTool();

        $response = $this->actingAs($user)->post('/keyword-cluster', [
            'topic' => 'content marketing',
            'website' => 'acme.com',
        ]);

        $project = KeywordProject::firstOrFail();
        $response->assertRedirect(route('keyword-cluster.show', $project));

        $this->assertSame($user->id, $project->user_id);
        $this->assertSame('content marketing', $project->topic);
        $this->assertSame(KeywordProject::STATUS_PENDING, $project->status);
        $this->assertSame('en', $project->language);

        Bus::assertDispatched(GenerateKeywordProjectJob::class, fn ($job) => $job->projectId === $project->id);
    }

    public function test_user_can_view_own_project(): void
    {
        [$user, $company] = $this->setupUserWithTool();

        $project = $company->keywordProjects()->create([
            'user_id' => $user->id,
            'topic' => 'seo strategy',
            'website' => 'acme.com',
            'status' => KeywordProject::STATUS_COMPLETED,
        ]);

        $this->actingAs($user)
            ->get(route('keyword-cluster.show', $project))
            ->assertOk()
            ->assertViewIs('keyword-cluster.show');
    }

    public function test_user_cannot_view_other_company_project(): void
    {
        [$user, $company] = $this->setupUserWithTool();
        $otherUser = User::factory()->create();

        $otherCompany = Company::create([
            'user_id' => $otherUser->id,
            'name' => 'Other Company',
            'slug' => Str::slug('Other Company').'-'.Str::random(6),
        ]);

        $otherCompany->users()->attach($otherUser->id, [
            'role' => Company::ROLE_OWNER,
            'is_default' => true,
        ]);

        $project = $otherCompany->keywordProjects()->create([
            'user_id' => $otherUser->id,
            'topic' => 'other topic',
            'website' => 'other.com',
            'status' => KeywordProject::STATUS_COMPLETED,
        ]);

        $this->actingAs($user)
            ->get(route('keyword-cluster.show', $project))
            ->assertForbidden();
    }

    public function test_status_endpoint_returns_progress(): void
    {
        [$user, $company] = $this->setupUserWithTool();

        $project = $company->keywordProjects()->create([
            'user_id' => $user->id,
            'topic' => 'test',
            'website' => 'test.com',
            'status' => KeywordProject::STATUS_GENERATING_QUESTIONS,
        ]);

        $this->actingAs($user)
            ->getJson(route('keyword-cluster.status', $project))
            ->assertOk()
            ->assertJson([
                'id' => $project->id,
                'status' => 'generating_questions',
                'is_in_progress' => true,
            ]);
    }
}
