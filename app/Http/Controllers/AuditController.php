<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function start(Request $request)
    {
        $company = auth()->user()->currentCompany();

        if (! $company) {
            return back()->with('error', 'You must belong to a company to start an audit.');
        }

        $request->validate([
            'template_id' => 'required|exists:audit_templates,id',
        ]);

        $audit = Audit::create([
            'company_id' => $company->id,
            'template_id' => $request->template_id,
            'created_by' => auth()->id(),
            'status' => 'in_progress',
        ]);

        return redirect()->route('audit.assessment', $audit);
    }

    public function results(Audit $audit)
    {
        // Allow access if user created this audit OR belongs to same company
        $user = auth()->user();
        $sameCompany = $user->currentCompany()?->id === $audit->company_id;
        $isCreator = $audit->created_by === $user->id;
        if (! $sameCompany && ! $isCreator) {
            abort(403);
        }

        $audit->load('results', 'company');

        // Calculate overall average
        $overallScore = $audit->results->avg('average_score') ?? 0;

        $overallMaturity = 'Beginner';
        if ($overallScore >= 4.5) {
            $overallMaturity = 'Excellent';
        } elseif ($overallScore >= 3.5) {
            $overallMaturity = 'Strong';
        } elseif ($overallScore >= 2.5) {
            $overallMaturity = 'Solid';
        } elseif ($overallScore >= 1.5) {
            $overallMaturity = 'Weak';
        } else {
            $overallMaturity = 'Critical';
        }

        return view('audit.results', compact('audit', 'overallScore', 'overallMaturity'));
    }

    public function report(Audit $audit)
    {
        $user = auth()->user();
        $sameCompany = $user->currentCompany()?->id === $audit->company_id;
        $isCreator = $audit->created_by === $user->id;
        if (! $sameCompany && ! $isCreator) {
            abort(403);
        }

        $audit->load('results', 'company', 'creator');

        $overallScore = $audit->results->avg('average_score') ?? 0;

        $overallMaturity = 'Beginner';
        if ($overallScore >= 4.5) {
            $overallMaturity = 'Excellent';
        } elseif ($overallScore >= 3.5) {
            $overallMaturity = 'Strong';
        } elseif ($overallScore >= 2.5) {
            $overallMaturity = 'Solid';
        } elseif ($overallScore >= 1.5) {
            $overallMaturity = 'Weak';
        } else {
            $overallMaturity = 'Critical';
        }

        return view('audit.report', compact('audit', 'overallScore', 'overallMaturity'));
    }

    public function destroy(Audit $audit)
    {
        $user = auth()->user();
        $sameCompany = $user->currentCompany()?->id === $audit->company_id;
        $isCreator = $audit->created_by === $user->id;
        if (! $sameCompany && ! $isCreator) {
            abort(403);
        }

        $audit->delete();

        return back()->with('success', __('Audit deleted successfully.'));
    }
}
