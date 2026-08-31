<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestCustomRequestRevisionRequest;
use App\Http\Requests\StoreCustomRequestRequest;
use App\Models\CustomBouquetCategory;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Services\CustomRequestService;
use App\Services\CustomRequestWhatsAppService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomRequestController extends Controller
{
    public function __construct(
        private CustomRequestService $customRequestService,
        private CustomRequestWhatsAppService $customRequestWhatsAppService,
    ) {}

    public function index(Request $request): View
    {
        $customRequests = CustomRequest::query()
            ->whereBelongsTo($this->customerFromRequest($request), 'customer')
            ->with(['customBouquetCategory:id,name,slug', 'product:id,name,slug'])
            ->latest()
            ->paginate(10);

        return view('customer.custom-requests.index', ['customRequests' => $customRequests]);
    }

    public function create(Request $request): View
    {
        $customBouquetCategories = CustomBouquetCategory::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'description', 'quantity_label', 'quote_threshold']);

        return view('customer.custom-requests.create', [
            'customBouquetCategories' => $customBouquetCategories,
            'selectedCategorySlug' => $request->string('category')->toString(),
        ]);
    }

    public function show(Request $request, CustomRequest $customRequest): View
    {
        $customRequest = $this->ownedCustomRequest($request, $customRequest);
        $this->customRequestService->expireIfQuoteExpired($customRequest);
        $customRequest->refresh()->load([
            'histories' => fn ($query) => $query->with(['admin:id,name', 'customer:id,name']),
            'customer:id,name,phone',
            'customBouquetCategory:id,name,slug',
            'items',
            'product:id,name,slug',
        ]);

        return view('customer.custom-requests.show', [
            'customRequest' => $customRequest,
            'followUpWhatsAppUrl' => $this->customRequestWhatsAppService->customerFollowUpUrl($customRequest),
            'referenceImageUrl' => $customRequest->reference_image_path ? route('customer.custom-requests.reference', $customRequest) : null,
        ]);
    }

    public function store(StoreCustomRequestRequest $request): RedirectResponse
    {
        $customRequest = $this->customRequestService->create(
            $this->customerFromRequest($request),
            CustomBouquetCategory::query()->findOrFail($request->validated('custom_bouquet_category_id')),
            $request->validated(),
            $request->file('reference_image'),
        );

        return redirect()->route('customer.custom-requests.show', $customRequest)->with('success', 'Permintaan Custom Bouquet berhasil dikirim. Kami akan menyiapkan penawarannya.');
    }

    public function requestRevision(RequestCustomRequestRevisionRequest $request, CustomRequest $customRequest): RedirectResponse
    {
        $customRequest = $this->customRequestService->requestRevision(
            $this->ownedCustomRequest($request, $customRequest),
            $this->customerFromRequest($request),
            $request->validated('revision_note'),
            $request->validated('counter_offer'),
        );

        return redirect()->route('customer.custom-requests.show', $customRequest)->with('success', 'Permintaan revisi sudah dikirim ke admin.');
    }

    public function approve(Request $request, CustomRequest $customRequest): RedirectResponse
    {
        $this->customRequestService->approveAndAddToCart(
            $this->ownedCustomRequest($request, $customRequest),
            $this->customerFromRequest($request),
        );

        return redirect()->route('cart.index')->with('success', 'Penawaran Custom Bouquet disetujui dan sudah ditambahkan ke keranjang.');
    }

    public function referenceImage(Request $request, CustomRequest $customRequest): StreamedResponse
    {
        $customRequest = $this->ownedCustomRequest($request, $customRequest);
        abort_unless($customRequest->reference_image_path, 404);

        return Storage::disk('local')->response($customRequest->reference_image_path);
    }

    private function customerFromRequest(Request $request): Customer
    {
        $customer = $request->user('customer');

        abort_unless($customer instanceof Customer, 403);

        return $customer;
    }

    private function ownedCustomRequest(Request $request, CustomRequest $customRequest): CustomRequest
    {
        return CustomRequest::query()->whereBelongsTo($this->customerFromRequest($request), 'customer')->findOrFail($customRequest->id);
    }
}
