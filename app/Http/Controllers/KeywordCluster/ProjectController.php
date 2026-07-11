<?php

namespace App\Http\Controllers\KeywordCluster;

use App\Http\Controllers\Controller;
use App\Http\Requests\KeywordCluster\StoreProjectRequest;
use App\Jobs\GenerateKeywordProjectJob;
use App\Models\KeywordProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    public function index()
    {
        $company = Auth::user()?->currentCompany();
        $geminiConfigured = ! empty(config('services.gemini.api_key'));

        $projects = $company
            ? $company->keywordProjects()->orderByDesc('id')->paginate(10)
            : new LengthAwarePaginator([], 0, 10);

        return view('keyword-cluster.index', compact('projects', 'geminiConfigured'));
    }

    public function create()
    {
        $geminiConfigured = ! empty(config('services.gemini.api_key'));

        return view('keyword-cluster.create', compact('geminiConfigured'));
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $company = Auth::user()?->currentCompany();

        if (! $company) {
            abort(403);
        }

        $supported = config('app.available_locales', ['en', 'de']);
        $locale = App::getLocale();
        if (! in_array($locale, $supported, true)) {
            $locale = 'en';
        }

        $project = $company->keywordProjects()->create([
            'user_id' => Auth::id(),
            'topic' => $request->string('topic'),
            'website' => $request->string('website'),
            'language' => $locale,
            'status' => KeywordProject::STATUS_PENDING,
        ]);

        GenerateKeywordProjectJob::dispatch($project->id);

        return redirect()->route('keyword-cluster.show', $project);
    }

    public function show(KeywordProject $project)
    {
        $this->authorizeProject($project);

        $project->load(['subtopics.questions']);
        $geminiConfigured = ! empty(config('services.gemini.api_key'));

        return view('keyword-cluster.show', compact('project', 'geminiConfigured'));
    }

    public function status(KeywordProject $project)
    {
        $this->authorizeProject($project);

        return response()->json([
            'id' => $project->id,
            'status' => $project->status,
            'status_label' => $project->statusLabel(),
            'progress_percent' => $project->progressPercent(),
            'is_in_progress' => $project->isInProgress(),
            'error' => $project->error,
        ]);
    }

    public function retry(KeywordProject $project): RedirectResponse
    {
        $this->authorizeProject($project);

        $project->update([
            'status' => KeywordProject::STATUS_PENDING,
            'error' => null,
        ]);

        GenerateKeywordProjectJob::dispatch($project->id);

        return redirect()->route('keyword-cluster.show', $project);
    }

    public function destroy(KeywordProject $project): RedirectResponse
    {
        $this->authorizeProject($project);
        $project->delete();

        return redirect()->route('keyword-cluster.index');
    }

    public function exportPillar(KeywordProject $project)
    {
        $this->authorizeProject($project);

        $filename = sprintf('pillar-%s.md', Str::slug($project->topic ?: 'page'));
        $body = sprintf(
            "<!--\nTitle: %s\nMeta Description: %s\n-->\n\n%s\n",
            $project->pillar_title ?? '',
            $project->pillar_meta_description ?? '',
            $project->pillar_content ?? ''
        );

        return response($body, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    public function exportCluster(KeywordProject $project, int $subtopic)
    {
        $this->authorizeProject($project);

        $sub = $project->subtopics()->findOrFail($subtopic);

        $filename = sprintf(
            'cluster-%s.md',
            Str::slug($sub->long_tail_keyword ?: $sub->title ?: 'page')
        );

        $body = sprintf(
            "<!--\nTitle: %s\nMeta Description: %s\nLong-tail keyword: %s\n-->\n\n%s\n",
            $sub->cluster_title ?? '',
            $sub->cluster_meta_description ?? '',
            $sub->long_tail_keyword ?? '',
            $sub->cluster_content ?? ''
        );

        return response($body, 200, [
            'Content-Type' => 'text/markdown; charset=utf-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ]);
    }

    protected function authorizeProject(KeywordProject $project): void
    {
        $company = Auth::user()?->currentCompany();

        if (! $company || $project->company_id !== $company->id) {
            abort(403);
        }
    }
}
