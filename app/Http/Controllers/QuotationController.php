<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use Illuminate\Http\Request;
use PDF;

class QuotationController extends Controller
{
    public function index()
    {
        $quotations = Quotation::latest()->get();
        return view('quotations.index', compact('quotations'));
    }

    public function create()
    {
        return view('quotations.create');
    }

    public function store(Request $request)
{
    $validated = $request->validate([
        'client_name' => 'required|string',
        'client_address' => 'required|string',
        'quotation_date' => 'required|date',
        'schedule' => 'required|date',
        'items' => 'required|array',
        'items.*.description' => 'required|string',
        'items.*.pricePerItem' => 'nullable|numeric',
        'items.*.pax' => 'nullable|numeric',
        'items.*.quantity' => 'nullable|numeric',
        'service_charge' => 'required|numeric',
        'total_amount' => 'required|numeric',
        'comments' => 'nullable|array'
    ]);

    // Calculate amount for each item and format items array
    $items = collect($request->items)->map(function ($item) {
        // Check for different possible key variations
        $pricePerItem = isset($item['price_per_item']) ? floatval($item['price_per_item']) : 
                        (isset($item['pricePerItem']) ? floatval($item['pricePerItem']) : null);
        
        $pax = isset($item['pax']) ? intval($item['pax']) : null;
        $quantity = isset($item['quantity']) ? intval($item['quantity']) : null;
    
        // Calculate amount only if all required values are present
        $amount = null;
        if ($pricePerItem && $pax && $quantity) {
            $amount = $pricePerItem * $pax * $quantity;
        }
    
        return [
            'description' => $item['description'] ?? '',
            'pricePerItem' => $pricePerItem,
            'pax' => $pax,
            'quantity' => $quantity,
            'amount' => $amount
        ];
    })->toArray();

    $quotation = Quotation::create([
        'client_name' => $validated['client_name'],
        'client_address' => $validated['client_address'],
        'quotation_date' => $validated['quotation_date'],
        'schedule' => $validated['schedule'],
        'items' => $items,
        'service_charge' => floatval($validated['service_charge']),
        'total_amount' => floatval($validated['total_amount']),
        'comments' => $request->comments ?? [
            'Cash payment or Online bank Transfer only accepted.',
            'Please provide the confirmed guest count to the hotel at least two days in advance.',
            'All meals are served buffet-style.'
        ]
    ]);

    return redirect()->route('quotations.show', $quotation)
        ->with('success', 'Quotation created successfully.');
}

    public function show(Quotation $quotation)
    {
        return view('quotations.show', compact('quotation'));
    }

    public function edit(Quotation $quotation)
    {
        return view('quotations.edit', compact('quotation'));
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validated = $request->validate([
            'client_name' => 'required|string',
            'client_address' => 'required|string',
            'quotation_date' => 'required|date',
            'schedule' => 'required|date',
            'items' => 'required|array',
            'items.*.description' => 'required|string',
            'service_charge' => 'nullable|numeric',
            'total_amount' => 'required|numeric',
            'comments' => 'nullable|array'
        ]);

        $quotation->update($validated);

        return redirect()->route('quotations.show', $quotation)
            ->with('success', 'Quotation updated successfully.');
    }

    public function destroy(Quotation $quotation)
    {
        $quotation->delete();
        return redirect()->route('quotations.index')
            ->with('success', 'Quotation deleted successfully.');
    }

    public function print(Quotation $quotation)
{
    // Transform items to ensure all fields are properly formatted
    $quotation->items = collect($quotation->items)->map(function ($item) {
        return [
            'description' => $item['description'] ?? '',
            'pricePerItem' => isset($item['pricePerItem']) ? number_format($item['pricePerItem'], 2) : '', // Match the casing from the form
            'pax' => $item['pax'] ?? '',
            'quantity' => $item['quantity'] ?? '',
            'amount' => isset($item['amount']) ? number_format($item['amount'], 2) : ''
        ];
    })->toArray();

    $pdf = PDF::loadView('quotations.print', [
        'quotation' => $quotation
    ])->setPaper('a4', 'portrait');
    
    $pdf->getDomPDF()->set_option('enable_remote', true);
    $pdf->getDomPDF()->set_option('isRemoteEnabled', true);

    return $pdf->stream("quotation-{$quotation->id}.pdf");
}
   

    public function convertToBooking(Quotation $quotation)
    {
        try {
            // Create new booking from quotation
            $booking = Booking::create([
                'client_name' => $quotation->client_name,
                'client_address' => $quotation->client_address,
                'schedule' => $quotation->schedule,
                'package_details' => $quotation->items,
                'total_amount' => $quotation->total_amount,
                'status' => 'confirmed'
            ]);
    
            // Update quotation status
            $quotation->update(['status' => 'converted']);
    
            return redirect()->route('bookings.show', $booking)
                ->with('success', 'Quotation successfully converted to booking.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to convert quotation to booking: ' . $e->getMessage());
        }
    }
}