<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\RequestCustomRequestRevisionRequest;
use App\Http\Requests\StoreCustomRequestRequest;
use App\Models\Customer;
use App\Models\CustomRequest;
use App\Models\Product;
use App\Services\CustomRequestService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomRequestController extends Controller
{
    public function __construct(private CustomRequestService $customRequestService) {}

    public function index(Request $request): View
    {
        $customRequests = CustomRequest::query()
            ->whereBelongsTo($this->customerFromRequest($request), 'customer')
            ->with('product:id,name,slug')
            ->latest()
            ->paginate(10);

        return view('customer.custom-requests.index', ['customRequests' => $customRequests]);
    }

    public function show(Request $request, CustomRequest $customRequest): View
    {
        $customRequest = $this->ownedCustomRequest($request, $customRequest);
        $this->customRequestService->expireIfQuoteExpired($customRequest);
        $customRequest->refresh()->load([
            'histories' => fn ($query) => $query->with(['admin:id,name', 'customer:id,name']),
            'items',
            'product:id,name,slug',
        ]);

        return view('customer.custom-requests.show', [
            'customRequest' => $customRequest,
            'referenceImageUrl' => $customRequest->reference_image_path ? route('customer.custom-requests.reference', $customRequest) : null,
        ]);
    }

    public function store(StoreCustomRequestRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_custom_request, 404);

        $customRequest = $this->customRequestService->create(
            $this->customerFromRequest($request),
            $product,
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
