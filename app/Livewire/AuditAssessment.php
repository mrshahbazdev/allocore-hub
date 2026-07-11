<?php

namespace App\Livewire;

use App\Models\Audit;
use App\Models\AuditAnswer;
use App\Models\AuditResult;
use App\Services\AllocoreKpiReporter;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.app')]
class AuditAssessment extends Component
{
    use WithFileUploads;

    public Audit $audit;

    public $currentStep = 1;

    public $answers = [];

    public $pillars = [];

    public function mount(Audit $audit)
    {
        $this->audit = $audit->load('template.pillars.questions');

        // Allow access if user created this audit OR belongs to same company
        $user = auth()->user();
        $sameCompany = $user->currentCompany()?->id === $audit->company_id;
        $isCreator = $audit->created_by === $user->id;

        if (! $sameCompany && ! $isCreator) {
            abort(403);
        }

        // Handle case where template might be missing (for older audits)
        if (! $this->audit->template) {
            abort(404, 'No template found for this audit session.');
        }

        $this->pillars = $this->audit->template->pillars()->orderBy('order')->get();

        // Initialize empty answers for all questions
        foreach ($this->pillars as $pillar) {
            foreach ($pillar->questions as $q) {
                $this->answers[$q->id] = [
                    'score' => null,
                    'comment' => '',
                ];
            }
        }

        // Load existing answers
        $existingAnswers = AuditAnswer::where('audit_id', $audit->id)->get();
        foreach ($existingAnswers as $answer) {
            if (isset($this->answers[$answer->question_id])) {
                $this->answers[$answer->question_id] = [
                    'score' => $answer->score,
                    'comment' => $answer->comment,
                ];
            }
        }

        // Find the user's progress based on existing answers
        $this->determineCurrentStep();
    }

    protected function determineCurrentStep()
    {
        // Go through each level and see if all questions are answered
        foreach ($this->pillars as $index => $pillar) {
            $questionsInLevel = $pillar->questions;
            $allAnswered = true;

            foreach ($questionsInLevel as $q) {
                if (empty($this->answers[$q->id]['score'])) {
                    $allAnswered = false;
                    break;
                }
            }

            if (! $allAnswered) {
                $this->currentStep = $index + 1;

                return;
            }
        }

        // If all answered, default to the last step to allow review before finish
        $this->currentStep = count($this->pillars);
    }

    public function getCurrentLevelName(): string
    {
        $pillar = $this->pillars[$this->currentStep - 1] ?? null;

        return $pillar ? $pillar->name : '';
    }

    public function getCurrentQuestions()
    {
        $pillar = $this->pillars[$this->currentStep - 1] ?? null;
        if (! $pillar) {
            return collect();
        }

        // Filter out questions that don't meet their dependencies
        return $pillar->questions->filter(function ($question) {
            if (! $question->depends_on_question_id) {
                return true; // No dependencies, show it
            }

            // Check if the parent question was answered
            $parentAnswerData = $this->answers[$question->depends_on_question_id] ?? null;
            $parentScore = $parentAnswerData['score'] ?? null;

            if ($parentScore === null || $parentScore === '') {
                return false; // Parent wasn't answered
            }

            // If there's a specific trigger value required
            if ($question->depends_on_answer !== null && $question->depends_on_answer !== '') {
                // strict check against string representation for arrays and scalars
                if (is_array($parentScore)) {
                    return in_array($question->depends_on_answer, $parentScore);
                }

                return (string) $parentScore === (string) $question->depends_on_answer;
            }

            return true; // Parent answered, no strict trigger value needed
        });
    }

    public function setScore(int $questionId, $score): void
    {
        if (isset($this->answers[$questionId])) {
            $this->answers[$questionId]['score'] = $score;
        }
    }

    public function toggleCheckbox($questionId, $option)
    {
        if (! isset($this->answers[$questionId]['score']) || ! is_array($this->answers[$questionId]['score'])) {
            $this->answers[$questionId]['score'] = [];
        }

        $scores = $this->answers[$questionId]['score'];
        if (($key = array_search($option, $scores)) !== false) {
            unset($scores[$key]);
        } else {
            $scores[] = $option;
        }

        $this->answers[$questionId]['score'] = array_values($scores); // re-index
    }

    public function nextStep()
    {
        // Validate all required questions in current step are answered
        $unanswered = [];
        foreach ($this->getCurrentQuestions() as $q) {
            if (! $q->is_required) {
                continue;
            }

            $score = $this->answers[$q->id]['score'] ?? null;
            // Allow 0 for 'no' and any string/array for 'text_input', 'select', 'checkbox', 'file_upload'
            if ($score === null || $score === '' || (is_array($score) && empty($score))) {
                $unanswered[] = $q->id;
            }
        }

        if (! empty($unanswered)) {
            session()->flash('error', 'Please answer all required questions before proceeding.');

            return;
        }

        $this->saveCurrentStep();

        if ($this->currentStep < count($this->pillars)) {
            $this->currentStep++;
        } else {
            return $this->finishAudit();
        }
    }

    public function previousStep()
    {
        $this->saveCurrentStep();

        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    public function saveDraft()
    {
        $this->saveCurrentStep();

        return redirect()->route('dashboard');
    }

    protected function saveCurrentStep()
    {
        foreach ($this->getCurrentQuestions() as $q) {
            $data = $this->answers[$q->id] ?? null;
            if ($data && ($data['score'] !== null && $data['score'] !== '' && (! is_array($data['score']) || ! empty($data['score'])))) {

                $scoreValue = $data['score'];
                $selectedOptions = null;
                $evidencePath = null;

                if ($q->question_type === 'checkbox' || $q->question_type === 'select' || $q->question_type === 'radio') {
                    $selectedOptions = is_array($scoreValue) ? $scoreValue : [$scoreValue];
                    $scoreValue = null; // Don't store arrays in score column
                } elseif ($q->question_type === 'file_upload') {
                    if (is_object($scoreValue) && method_exists($scoreValue, 'store')) {
                        $evidencePath = $scoreValue->store('audit_evidence', 'public');
                    } elseif (is_string($scoreValue)) {
                        $evidencePath = $scoreValue; // Already uploaded previously
                    }
                    $scoreValue = null;
                }

                AuditAnswer::updateOrCreate(
                    [
                        'audit_id' => $this->audit->id,
                        'question_id' => $q->id,
                    ],
                    [
                        'score' => $scoreValue,
                        'comment' => $data['comment'] ?? null,
                        'selected_options' => $selectedOptions,
                        'evidence_file_path' => $evidencePath,
                    ]
                );
            }
        }
    }

    protected function finishAudit()
    {
        $this->audit->update(['status' => 'completed']);

        // Calculate Results per pillar
        foreach ($this->pillars as $pillar) {
            $questions = $pillar->questions;
            $qIds = $questions->pluck('id');

            $answers = AuditAnswer::where('audit_id', $this->audit->id)
                ->whereIn('question_id', $qIds)
                ->get();

            // Calculate possible max score and achieved score
            $totalPossibleWeightedScore = 0;
            $achievedWeightedScore = 0;

            foreach ($answers as $ans) {
                // Ignore text_input, select, file_upload for scoring calculation
                $question = $questions->firstWhere('id', $ans->question_id);
                if (! $question || ! in_array($question->question_type, ['scale_1_to_5', 'yes_no', 'radio', 'checkbox'])) {
                    continue;
                }

                $weight = $question->weight ?? 1.0;
                $qType = $question->question_type;

                // Max base score is 5 points for any rated question
                $totalPossibleWeightedScore += (5 * $weight);

                // Calculate achieved base score before weight
                $baseScore = 0;
                if ($qType === 'yes_no') {
                    $baseScore = $ans->score == 1 ? 5 : 1;
                } elseif ($qType === 'scale_1_to_5' || $qType === 'radio') {
                    $baseScore = (float) $ans->score;
                } elseif ($qType === 'checkbox') {
                    // Checkboxes can be complex. We'll simply grade them as (num_selected / total_options) * 5
                    $optionsCount = is_array($question->options) ? count($question->options) : 1;
                    if ($optionsCount > 0 && is_array($ans->selected_options)) {
                        $selectedCount = count($ans->selected_options);
                        $baseScore = ($selectedCount / $optionsCount) * 5;
                    }
                }

                $achievedWeightedScore += ($baseScore * $weight);
            }

            // Normalise score out of 5 for consistent legacy reporting and radar chart
            $averageScore = $totalPossibleWeightedScore > 0
                ? ($achievedWeightedScore / $totalPossibleWeightedScore) * 5
                : 0;

            // Maturity level based on average score (1-5 mapped to levels)
            $maturityLevel = 'Beginner';
            if ($averageScore >= 4.5) {
                $maturityLevel = 'Excellent';
            } elseif ($averageScore >= 3.5) {
                $maturityLevel = 'Strong';
            } elseif ($averageScore >= 2.5) {
                $maturityLevel = 'Solid';
            } elseif ($averageScore >= 1.5) {
                $maturityLevel = 'Weak';
            } else {
                $maturityLevel = 'Critical';
            }

            AuditResult::updateOrCreate(
                [
                    'audit_id' => $this->audit->id,
                    'level' => $pillar->name, // Using pillar name for compatibility
                ],
                [
                    'average_score' => $averageScore,
                    'maturity_level' => $maturityLevel,
                    'total_points' => $achievedWeightedScore,
                ]
            );
        }

        // Push the results to the Allocore Hub as KPIs (no-op if not configured).
        app(AllocoreKpiReporter::class)->report($this->audit->fresh());

        // Redirect to results
        return redirect()->route('audit.results', $this->audit);
    }

    public function render()
    {
        return view('livewire.audit-assessment', [
            'currentLevelName' => $this->getCurrentLevelName(),
            'currentQuestions' => $this->getCurrentQuestions(),
        ]);
    }
}
