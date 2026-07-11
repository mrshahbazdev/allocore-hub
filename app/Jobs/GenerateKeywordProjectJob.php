<?php

namespace App\Jobs;

use App\Models\KeywordProject;
use App\Services\KeywordCluster\KeywordClusterGenerator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateKeywordProjectJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 1200;

    public int $tries = 1;

    public function __construct(public int $projectId) {}

    public function handle(KeywordClusterGenerator $generator): void
    {
        $project = KeywordProject::find($this->projectId);
        if (! $project) {
            return;
        }

        try {
            $project->update(['status' => KeywordProject::STATUS_GENERATING_SUBTOPICS, 'error' => null]);
            $generator->generateSubtopics($project);

            $project->update(['status' => KeywordProject::STATUS_GENERATING_QUESTIONS]);
            foreach ($project->subtopics()->get() as $subtopic) {
                $generator->generateQuestionsForSubtopic($subtopic);
            }

            $project->update(['status' => KeywordProject::STATUS_GENERATING_ANSWERS]);
            foreach ($project->subtopics()->get() as $subtopic) {
                $generator->generateAnswersForSubtopic($subtopic);
            }

            $project->update(['status' => KeywordProject::STATUS_GENERATING_PAGES]);
            foreach ($project->subtopics()->get() as $subtopic) {
                $generator->generateClusterPage($subtopic);
            }
            $generator->generatePillarPage($project->fresh());

            $project->update(['status' => KeywordProject::STATUS_COMPLETED]);
        } catch (Throwable $e) {
            Log::error('GenerateKeywordProjectJob failed', [
                'project_id' => $this->projectId,
                'message' => $e->getMessage(),
            ]);

            $project->update([
                'status' => KeywordProject::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function failed(Throwable $e): void
    {
        $project = KeywordProject::find($this->projectId);
        if ($project) {
            $project->update([
                'status' => KeywordProject::STATUS_FAILED,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
