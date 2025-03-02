<?php

namespace App\Http\Controllers;

use App\Models\Lender;
use Illuminate\Http\Request;

class LenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filter = $request->query('filter', 'unpaid');
        
        // Retrieve all lenders from the database
        $query = Lender::orderBy('created_at', 'desc');
        
        // Filter based on paid status if requested
        if ($filter === 'paid') {
            $query->where('is_paid', true);
        } elseif ($filter === 'unpaid') {
            $query->where('is_paid', false);
        }
        
        $lenders = $query->get();
        
        // Return the index view, passing the list of lenders
        return view('lenders.index', compact('lenders', 'filter'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('lenders.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Validate incoming data
        $request->validate([
            'name'       => 'required|string',
            'nic_number' => 'required|string',
            'bill_number' => 'nullable|string',
            'description'=> 'nullable|string',
            'amount'     => 'nullable|numeric',
            'date'       => 'nullable|date',
        ]);

        // Create a new Lender record
        Lender::create($request->all());

        // Redirect back with a success message
        return redirect()->route('lenders.index')
                         ->with('success', 'Lender created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Lender $lender)
    {
        return view('lenders.show', compact('lender'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Lender $lender)
    {
        return view('lenders.edit', compact('lender'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Lender $lender)
    {
        // Validate incoming data
        $request->validate([
            'name'       => 'required|string',
            'nic_number' => 'required|string',
            'bill_number' => 'nullable|string',
            'description'=> 'nullable|string',
            'amount'     => 'nullable|numeric',
            'date'       => 'nullable|date',
        ]);

        // Update the Lender record
        $lender->update($request->all());

        // Redirect back with a success message
        return redirect()->route('lenders.index')
                         ->with('success', 'Lender updated successfully.');
    }

    /**
     * Mark a lender as paid.
     */
    public function markAsPaid($id)
    {
        $lender = Lender::findOrFail($id);
        $lender->is_paid = true;
        $lender->paid_at = now();
        $lender->save();

        return redirect()->route('lenders.index')
                         ->with('success', 'Lender marked as paid successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Lender $lender)
    {
        $lender->delete();

        return redirect()->route('lenders.index')
                         ->with('success', 'Lender deleted successfully.');
    }
}