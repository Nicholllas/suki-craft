<?php

namespace App\Http\Controllers;

use App\Http\Requests\TrackOrderRequest;
use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TrackingController extends Controller
{
    public function create(): View
    {
        return view('tracking.index');
    }

    public function store(TrackOrderRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $order = Order::query()
            ->where('order_number', $data['order_number'])
            ->where('customer_phone', $data['phone'])
            ->first();

        if (! $order) {
            throw ValidationException::withMessages(['order_number' => 'Nomor pesanan atau nomor WhatsApp tidak cocok.']);
        }

        $request->session()->put('tracked_order_id', $order->id);

        return redirect()->route('tracking.show', $order);
    }

    public function show(Order $order): View
    {
        $this->ensureOrderWasTracked($order);
        $order->load([
            'courier:id,name,phone',
            'items.review',
            'statusHistories' => fn ($query) => $query->orderBy('created_at')->orderBy('id'),
        ]);

        return view('tracking.show', [
            'deliveryProofUrl' => $order->delivery_proof_path ? route('tracking.delivery-proofs.show', $order) : null,
            'order' => $order,
        ]);
    }

    public function deliveryProof(Order $order): StreamedResponse
    {
        $this->ensureOrderWasTracked($order);
        abort_unless($order->delivery_proof_path, 404);

        return Storage::disk('local')->response($order->delivery_proof_path);
    }

    private function ensureOrderWasTracked(Order $order): void
    {
        abort_unless(session('tracked_order_id') === $order->id, 404);
    }
}
