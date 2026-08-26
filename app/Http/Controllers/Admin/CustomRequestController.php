<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\QuoteCustomRequestRequest;
use App\Http\Requests\Admin\RejectCustomRequestRequest;
use App\Models\Admin;
use App\Models\CustomRequest;
use App\Services\CustomRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomRequestController extends Controller
{
    public function __construct(private CustomRequestService $customRequestService) {}

    public function index(): View
    {
        $customRequests = CustomRequest::query()
            ->with(['customer:id,name,phone', 'product:id,name,slug'])
            ->latest()
            ->paginate(15);

        return view('admin.custom-requests.index', ['customRequests' => $customRequests]);
    }

    public function show(CustomRequest $customRequest): View
    {
        $this->customRequestService->expireIfQuoteExpired($customRequest);
        $customRequest->refresh()->load([
            'customer:id,name,email,phone',
            'histories' => fn ($query) => $query->with(['admin:id,name', 'customer:id,name']),
            'items',
            'product:id,name,slug',
        ]);

        return view('admin.custom-requests.show', [
            'customRequest' => $customRequest,
            'referenceImageUrl' => $customRequest->reference_image_path ? route('admin.custom-requests.reference', $customRequest) : null,
        ]);
    }

    public function quote(QuoteCustomRequestRequest $request, CustomRequest $customRequest): RedirectResponse
    {
        $this->customRequestService->quote($customRequest, $request->validated(), $this->adminFromRequest($request));

        return redirect()->route('admin.custom-requests.show', $customRequest)->with('success', 'Penawaran Custom Bouquet berhasil dikirim.');
    }

    public function reject(RejectCustomRequestRequest $request, CustomRequest $customRequest): RedirectResponse
    {
        $this->customRequestService->reject($customRequest, $request->validated('reason'), $this->adminFromRequest($request));

        return redirect()->route('admin.custom-requests.show', $customRequest)->with('success', 'Permintaan Custom Bouquet ditolak dengan catatan.');
    }

    public function referenceImage(CustomRequest $customRequest): StreamedResponse
    {
        abort_unless($customRequest->reference_image_path, 404);

        return Storage::disk('local')->response($customRequest->reference_image_path);
    }

    private function adminFromRequest(Request $request): Admin
    {
        $admin = $request->user('admin');

        abort_unless($admin instanceof Admin, 403);

        return $admin;
    }
}
