<?php

namespace App\Http\Controllers\Website;

use App\Actions\Leads\CreateLead;
use App\Http\Controllers\Controller;
use App\Models\LeadFormField;
use App\Models\Service;
use App\Services\LeadWorkflowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class QuoteController extends Controller
{
    public function store(Request $request, CreateLead $create, LeadWorkflowService $workflow): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:190'],
            'phone' => ['required', 'string', 'max:32'],
            'email' => ['nullable', 'email', 'max:190'],
            'message' => ['nullable', 'string', 'max:4000'],
            'service_id' => ['nullable', 'integer', Rule::exists('services', 'id')->where('status', 'published')->whereNull('deleted_at')],
            'dynamic_answers' => ['sometimes', 'array'],
            'dynamic_answers.*' => ['nullable', 'string', 'max:500'],
            'company_website' => ['nullable', 'size:0'], // Honeypot
        ]);

        if (! empty($data['service_id']) && ! Service::query()->published()->whereKey($data['service_id'])->exists()) {
            throw \Illuminate\Validation\ValidationException::withMessages(['service_id' => 'This service is not published.']);
        }
        $phone = $workflow->normalizePhone($data['phone']);
        $answers = (array) ($data['dynamic_answers'] ?? []);
        $allowed = LeadFormField::query()->active()->pluck('field_key')->all();
        if (array_diff(array_keys($answers), $allowed)) {
            throw \Illuminate\Validation\ValidationException::withMessages(['dynamic_answers' => 'One or more form fields are unavailable.']);
        }

        $create->execute(
            attributes: [
                'name' => trim($data['name']),
                'phone' => $phone,
                'email' => $data['email'] ?? null,
                'message' => $data['message'] ?? null,
                'source_page_url' => '/',
                'status' => 'new',
                'consent' => ['marketing' => false],
            ],
            dynamicAnswers: $answers,
            serviceIds: ! empty($data['service_id']) ? [(int) $data['service_id']] : [],
        );

        return back()->with('quote_success', 'Thank you. Your enquiry has been received.');
    }
}
