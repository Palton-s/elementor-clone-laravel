<?php

namespace App\Http\Controllers\PageBuilder;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessFormSubmissionJob;
use App\Models\Page;
use App\Models\FormSubmission;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function submit(Request $request, Page $page)
    {
        abort_unless($page->status === 'published', 404);

        $validated = $request->validate([
            'form_name' => 'nullable|string|max:255',
            'data' => 'required|array',
            'data.*' => 'nullable|string|max:5000',
        ]);

        $data = array_map(
            fn ($value) => is_string($value) ? strip_tags($value) : $value,
            $validated['data']
        );

        $submission = $page->formSubmissions()->create([
            'form_name' => strip_tags($validated['form_name'] ?? 'Contact Form'),
            'data' => $data,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        ProcessFormSubmissionJob::dispatch($page, $submission);

        return response()->json([
            'success' => true,
            'message' => 'Formulário enviado',
        ]);
    }

    public function submissions(Request $request, Page $page)
    {
        $this->authorize('view', $page);

        $submissions = $page->formSubmissions()->paginate(20);

        return response()->json($submissions);
    }
}
