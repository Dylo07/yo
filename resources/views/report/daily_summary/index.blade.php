@extends('layouts.app')

@section('content')
@php
    $currentUser = Auth::user();
    $isAdmin = $currentUser && $currentUser->checkAdmin();
    $today = Carbon\Carbon::today()->format('d.m.Y');
    $yesterday = Carbon\Carbon::yesterday()->format('d.m.Y');
@endphp
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            @if($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{$error}}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="/home">Main Functions</a></li>
                    <li class="breadcrumb-item"><a href="/report">Report</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Daily Sale Summary</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row mb-3">
        @if($isAdmin)
        <!-- Date selector for admin users -->
        <div class="col-md-4">
            <div class="form-group">
                <label for="date-picker">Select Date</label>
                <div class="input-group date" id="date-picker" data-target-input="nearest">
                    <input type="text" id="selected-date" class="form-control datetimepicker-input" data-target="#date-picker"/>
                    <div class="input-group-append" data-target="#date-picker" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button id="load-summary" class="btn btn-primary">Load Summary</button>
        </div>
        <div class="col-md-6 text-right">
            <h3 id="summary-date">Daily Sale Summary - {{ $today }}</h3>
        </div>
        @else
        <!-- Today/Yesterday buttons for non-admin users -->
        <div class="col-md-6">
            <div class="btn-group" role="group" aria-label="Date Selection">
                <button type="button" class="btn btn-outline-primary date-btn" data-date="{{ $today }}">Today</button>
                <button type="button" class="btn btn-outline-secondary date-btn" data-date="{{ $yesterday }}">Yesterday</button>
            </div>
            <input type="hidden" id="selected-date" value="{{ $today }}">
        </div>
        <div class="col-md-6 text-right">
            <h3 id="summary-date">Daily Sale Summary - {{ $today }}</h3>
        </div>
        @endif
    </div>

    <div class="table-responsive">
        <table id="daily-summary-table" class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>Date/Time</th>
                    <th>Bill Number</th>
                    <th>Rooms</th>
                    <th>Swimming Pool</th>
                    <th>Arrack</th>
                    <th>Beer</th>
                    <th>Other</th>
                    <th>Service Charge</th>
                    <th>Description</th>
                    <th>Total</th>
                    <th>Cash Payment</th>
                    <th>Card Payment</th>
                    <th>Bank Payment</th>
                    <th>Status (Pay or unpay)</th>
                    @if($isAdmin)
                        <th>Verify</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                <!-- Sales data will be loaded here via JavaScript -->
            </tbody>
            <tfoot>
                <tr class="bg-light">
                    <td colspan="2" class="text-right"><strong>Total</strong></td>
                    <td id="total-rooms">0</td>
                    <td id="total-swimming">0</td>
                    <td id="total-arrack">0</td>
                    <td id="total-beer">0</td>
                    <td id="total-other">0</td>
                    <td id="total-service-charge">0</td>
                    <td></td>
                    <td id="total-amount">0</td>
                    <td id="total-cash">0</td>
                    <td id="total-card">0</td>
                    <td id="total-bank">0</td>
                    <td colspan="{{ $isAdmin ? '2' : '1' }}"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="row mt-3">
        <div class="col-md-4">
            <button id="add-row" class="btn btn-success">Add +</button>
        </div>
        <div class="col-md-8 text-right">
            <button id="save-summary" class="btn btn-primary mr-2">Save</button>
            <button id="print-summary" class="btn btn-info">Print</button>
        </div>
    </div>
</div>

<!-- Template for new rows -->
<template id="row-template">
    <tr class="manual-row">
        <td>
            <input type="text" class="form-control datetime-input" value="" readonly>
        </td>
        <td>
            <input type="text" class="form-control bill-number-input" value="">
        </td>
        <td>
            <input type="number" class="form-control rooms-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control swimming-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control arrack-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control beer-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control other-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control service-charge-input" value="0">
        </td>
        <td>
            <input type="text" class="form-control description-input" value="">
        </td>
        <td>
            <input type="number" class="form-control total-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control cash-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control card-input" value="0">
        </td>
        <td>
            <input type="number" class="form-control bank-input" value="0">
        </td>
        <td>
            <select class="form-control status-select">
                <option value="paid">Paid</option>
                <option value="unpaid" selected>Unpaid</option>
            </select>
        </td>
        @if($isAdmin)
        <td>
            <button class="btn btn-sm btn-primary verify-btn">Verify</button>
            <button class="btn btn-sm btn-danger remove-row">×</button>
        </td>
        @else
        <td>
            <button class="btn btn-sm btn-danger remove-row">×</button>
        </td>
        @endif
    </tr>
</template>


<!-- Include necessary scripts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/css/tempusdominus-bootstrap-4.min.css">

<meta name="csrf-token" content="{{ csrf_token() }}">

<script>
    $(function () {
        // Setup CSRF for all AJAX requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // Set admin status for use in JavaScript
        const isAdmin = {{ $isAdmin ? 'true' : 'false' }};

        // Initialize UI based on user role
        if (isAdmin) {
            // Initialize date picker for admin
            $('#date-picker').datetimepicker({
                format: 'DD.MM.YYYY',
                defaultDate: moment(),
            });

            // Bind click event for loading data (admin)
            $('#load-summary').click(function() {
                loadSummaryData();
            });
        } else {
            // For non-admin, bind click event to today/yesterday buttons
            $('.date-btn').click(function() {
                // Remove active class from all buttons
                $('.date-btn').removeClass('active btn-primary').addClass('btn-outline-primary');
                
                // Add active class to clicked button
                $(this).removeClass('btn-outline-primary').addClass('active btn-primary');
                
                // Set the selected date
                const selectedDate = $(this).data('date');
                $('#selected-date').val(selectedDate);
                
                // Load data for the selected date
                loadSummaryData();
            });
            
            // Activate the Today button by default
            $('.date-btn[data-date="{{ $today }}"]').click();
        }

        // Load data for the current date on page load
        loadSummaryData();

        // Add new row
        $('#add-row').click(function() {
        const rowTemplate = document.getElementById('row-template');
        const newRow = document.importNode(rowTemplate.content, true);
        
        // Set current date/time with proper format including time and make it read-only
        const currentDateTime = moment().format('MM/DD/YYYY HH:mm:ss');
        $(newRow).find('.datetime-input').val(currentDateTime);
        
        // Add the new row
        $('#daily-summary-table tbody').append(newRow);
        
        // Update totals
        updateTotals();
    });

        // Save summary
        $('#save-summary').click(function() {
            // Show loading indicator
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
            
            const summaryData = collectTableData();
            const selectedDate = $('#selected-date').val();
            
            // Add additional logging
            console.log('Submitting data to save:', {
                date: selectedDate,
                sales: summaryData
            });
            
            $.ajax({
                url: '/report/daily-summary/save',
                method: 'POST',
                data: {
                    date: selectedDate,
                    sales: summaryData
                },
                success: function(response) {
                    console.log('Save response:', response);
                    if (response.success) {
                        alert('Summary saved successfully!');
                        loadSummaryData(); // Reload to refresh the view
                    } else {
                        alert('Error saving summary: ' + response.message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Save error details:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    // Try to parse response if possible
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        alert('Error saving summary: ' + (errorResponse.message || 'Unknown error'));
                    } catch(e) {
                        alert('Error saving summary. Please check console for details.');
                    }
                },
                complete: function() {
                    // Re-enable the button
                    $('#save-summary').prop('disabled', false).html('Save');
                }
            });
        });

        // Print summary
        $('#print-summary').click(function() {
            const date = $('#selected-date').val();
            window.open(`/report/daily-summary/print?date=${date}`, '_blank');
        });

        // Verify button click handler (only for admins)
        if (isAdmin) {
    $(document).on('click', '.verify-btn', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const row = button.closest('tr');
        const billNumber = row.attr('data-sale-id') || row.find('.bill-number-input').val();
        const isVerifying = !row.hasClass('table-success');
        const selectedDate = $('#selected-date').val();
        
        // Show the values we're submitting in the console for debugging
        console.log('Verifying bill:', {
            billNumber: billNumber,
            isVerifying: isVerifying,
            selectedDate: selectedDate
        });
        
        // Update UI immediately for better user experience
        row.toggleClass('table-success');
        if (isVerifying) {
            button.removeClass('btn-primary').addClass('btn-success');
            button.text('Verified');
        } else {
            button.removeClass('btn-success').addClass('btn-primary');
            button.text('Verify');
        }
        
        // Set form values
        $('#verify-bill-number').val(billNumber);
        $('#verify-status').val(isVerifying ? 1 : 0);
        $('#verify-date').val(selectedDate);
        
        // Submit the form with traditional form submit
        const form = $('#verify-form');
        const formData = new FormData(form[0]);
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Verification success:', response);
                if (!response.success) {
                    // Revert UI changes if there was an error
                    row.toggleClass('table-success');
                    if (!isVerifying) {
                        button.removeClass('btn-primary').addClass('btn-success');
                        button.text('Verified');
                    } else {
                        button.removeClass('btn-success').addClass('btn-primary');
                        button.text('Verify');
                    }
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error('Verification error:', error);
                console.error('Response:', xhr.responseText);
                
                // Revert UI changes on error
                row.toggleClass('table-success');
                if (!isVerifying) {
                    button.removeClass('btn-primary').addClass('btn-success');
                    button.text('Verified');
                } else {
                    button.removeClass('btn-success').addClass('btn-primary');
                    button.text('Verify');
                }
                alert('Failed to save verification status. Please check the console for details.');
            }
        });
    });
}

        // Remove row handler
        $(document).on('click', '.remove-row', function() {
            $(this).closest('tr').remove();
            updateTotals();
        });

        // Update calculations when numbers change
        $(document).on('change', 'input[type=number]', function() {
            updateTotals();
            
            // If it's a payment input, update row's payment distribution
            if ($(this).hasClass('cash-input') || $(this).hasClass('card-input') || $(this).hasClass('bank-input')) {
                updateRowPaymentDistribution($(this).closest('tr'));
            }
            
            // If it's a category input, update row's total
            if ($(this).hasClass('rooms-input') || $(this).hasClass('swimming-input') || 
                $(this).hasClass('arrack-input') || $(this).hasClass('beer-input') || 
                $(this).hasClass('other-input')) {
                updateRowTotal($(this).closest('tr'));
            }
        });

        // Function to load summary data
        function loadSummaryData() {
            const date = $('#selected-date').val();
            
            // Update the summary date in the header
            $('#summary-date').text(`Daily Sale Summary - ${date}`);
            
            $.ajax({
                url: '/report/daily-summary/data',
                method: 'GET',
                data: {
                    date: date
                },
                success: function(response) {
                    console.log('Loaded summary data:', response);
                    
                    // Clear existing rows and add new ones
                    $('#daily-summary-table tbody').empty();
                    
                    if (response.sales && response.sales.length > 0) {
                        response.sales.forEach(function(sale) {
                            addSaleRow(sale);
                        });
                    } else {
                        console.log('No sales data found for date:', date);
                    }
                    
                    // Update the totals
                    updateTotals();
                },
                error: function(xhr, status, error) {
                    console.error('Error loading summary data:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText
                    });
                    
                    try {
                        const errorResponse = JSON.parse(xhr.responseText);
                        alert('Error loading summary data: ' + (errorResponse.message || 'Unknown error'));
                    } catch(e) {
                        alert('Error loading summary data. Please try again.');
                    }
                }
            });
        }

        // Function to add a sale row to the table
        function addSaleRow(sale) {
            console.log('Adding sale row:', sale);
            
            // Determine if this row is verified
            const isVerified = sale.verified ? true : false;
            
            // For manually added rows vs. system rows
            const isManual = sale.manual ? true : false;
            
            // Build the row HTML
            let rowHtml;
            
            if (isManual) {
                // For manual entries, use input fields for all values
                rowHtml = `
                <tr class="manual-row ${isVerified ? 'table-success' : ''}" data-verified="${isVerified ? '1' : '0'}">
                    <td>
                        <input type="text" class="form-control datetime-input" value="${sale.datetime || ''}">
                    </td>
                    <td>
                        <input type="text" class="form-control bill-number-input" value="${sale.id || ''}">
                    </td>
                    <td>
                        <input type="number" class="form-control rooms-input" value="${parseFloat(sale.rooms) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control swimming-input" value="${parseFloat(sale.swimming_pool) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control arrack-input" value="${parseFloat(sale.arrack) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control beer-input" value="${parseFloat(sale.beer) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control other-input" value="${parseFloat(sale.other) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control service-charge-input" value="${parseFloat(sale.service_charge) || 0}">
                    </td>
                    <td>
                        <input type="text" class="form-control description-input" value="${sale.description || ''}">
                    </td>
                    <td>
                        <input type="number" class="form-control total-input" value="${parseFloat(sale.total) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control cash-input" value="${parseFloat(sale.cash_payment) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control card-input" value="${parseFloat(sale.card_payment) || 0}">
                    </td>
                    <td>
                        <input type="number" class="form-control bank-input" value="${parseFloat(sale.bank_payment) || 0}">
                    </td>
                    <td>
                        <select class="form-control status-select">
                            <option value="paid" ${sale.status === 'paid' ? 'selected' : ''}>Paid</option>
                            <option value="unpaid" ${sale.status === 'unpaid' ? 'selected' : ''}>Unpaid</option>
                        </select>
                    </td>`;
            } else {
                // For system-generated rows, only make certain fields editable
                rowHtml = `
                <tr data-sale-id="${sale.id}" data-verified="${isVerified ? '1' : '0'}" class="${isVerified ? 'table-success' : ''}">
                    <td>${sale.datetime}</td>
                    <td>${sale.id}</td>
                    <td>${parseFloat(sale.rooms).toFixed(2)}</td>
                    <td>${parseFloat(sale.swimming_pool).toFixed(2)}</td>
                    <td>${parseFloat(sale.arrack).toFixed(2)}</td>
                    <td>${parseFloat(sale.beer).toFixed(2)}</td>
                    <td>${parseFloat(sale.other).toFixed(2)}</td>
                    <td>${parseFloat(sale.service_charge).toFixed(2)}</td>
                    <td><input type="text" class="form-control description-input" value="${sale.description || ''}"></td>
                    <td>${parseFloat(sale.total).toFixed(2)}</td>
                    <td><input type="number" class="form-control cash-input" value="${parseFloat(sale.cash_payment) || 0}"></td>
                    <td><input type="number" class="form-control card-input" value="${parseFloat(sale.card_payment) || 0}"></td>
                    <td><input type="number" class="form-control bank-input" value="${parseFloat(sale.bank_payment) || 0}"></td>
                    <td>
                        <select class="form-control status-select">
                            <option value="paid" ${sale.status === 'paid' ? 'selected' : ''}>Paid</option>
                            <option value="unpaid" ${sale.status === 'unpaid' ? 'selected' : ''}>Unpaid</option>
                        </select>
                    </td>`;
            }
            
            // Add verify button for admins
            if (isAdmin) {
                rowHtml += `
                    <td>
                        <button class="btn btn-sm ${isVerified ? 'btn-success' : 'btn-primary'} verify-btn">
                            ${isVerified ? 'Verified' : 'Verify'}
                        </button>
                        ${isManual ? '<button class="btn btn-sm btn-danger remove-row">×</button>' : ''}
                    </td>`;
            } else if (isManual) {
                rowHtml += `
                    <td>
                        <button class="btn btn-sm btn-danger remove-row">×</button>
                    </td>`;
            }
            
            rowHtml += `</tr>`;
            
            $('#daily-summary-table tbody').append(rowHtml);
        }

        // Function to collect all table data
        function collectTableData() {
            const rows = $('#daily-summary-table tbody tr');
            const data = [];
            
            rows.each(function() {
                const row = $(this);
                const isManualRow = row.hasClass('manual-row');
                
                // Check if verified
                let isVerified = row.hasClass('table-success') ? 1 : 0;
                
                // Different data extraction based on row type
                if (isManualRow) {
                    data.push({
                        id: row.find('.bill-number-input').val() || '',
                        datetime: row.find('.datetime-input').val() || '',
                        rooms: parseFloat(row.find('.rooms-input').val()) || 0,
                        swimming_pool: parseFloat(row.find('.swimming-input').val()) || 0,
                        arrack: parseFloat(row.find('.arrack-input').val()) || 0,
                        beer: parseFloat(row.find('.beer-input').val()) || 0,
                        other: parseFloat(row.find('.other-input').val()) || 0,
                        service_charge: parseFloat(row.find('.service-charge-input').val()) || 0,
                        description: row.find('.description-input').val() || '',
                        total: parseFloat(row.find('.total-input').val()) || 0,
                        cash_payment: parseFloat(row.find('.cash-input').val()) || 0,
                        card_payment: parseFloat(row.find('.card-input').val()) || 0,
                        bank_payment: parseFloat(row.find('.bank-input').val()) || 0,
                        status: row.find('.status-select').val() || 'unpaid',
                        verified: isVerified,
                        manual: true
                    });
                } else {
                    data.push({
                        id: row.attr('data-sale-id') || '',
                        datetime: row.find('td:eq(0)').text() || '',
                        rooms: parseFloat(row.find('td:eq(2)').text()) || 0,
                        swimming_pool: parseFloat(row.find('td:eq(3)').text()) || 0,
                        arrack: parseFloat(row.find('td:eq(4)').text()) || 0,
                        beer: parseFloat(row.find('td:eq(5)').text()) || 0,
                        other: parseFloat(row.find('td:eq(6)').text()) || 0,
                        service_charge: parseFloat(row.find('td:eq(7)').text()) || 0,
                        description: row.find('.description-input').val() || '',
                        total: parseFloat(row.find('td:eq(9)').text()) || 0,
                        cash_payment: parseFloat(row.find('.cash-input').val()) || 0,
                        card_payment: parseFloat(row.find('.card-input').val()) || 0,
                        bank_payment: parseFloat(row.find('.bank-input').val()) || 0,
                        status: row.find('.status-select').val() || 'unpaid',
                        verified: isVerified,
                        manual: false
                    });
                }
            });
            
            return data;
        }

        // Function to update totals
        function updateTotals() {
            let totalRooms = 0;
            let totalSwimming = 0;
            let totalArrack = 0;
            let totalBeer = 0;
            let totalOther = 0;
            let totalServiceCharge = 0;
            let totalAmount = 0;
            let totalCash = 0;
            let totalCard = 0;
            let totalBank = 0;
            
            $('#daily-summary-table tbody tr').each(function() {
                const row = $(this);
                const isManualRow = row.hasClass('manual-row');
                
                let rooms = 0;
                let swimming = 0;
                let arrack = 0;
                let beer = 0;
                let other = 0;
                let serviceCharge = 0;
                
                if (isManualRow) {
                    // For manual rows, get values from inputs
                    rooms = parseFloat(row.find('.rooms-input').val()) || 0;
                    swimming = parseFloat(row.find('.swimming-input').val()) || 0;
                    arrack = parseFloat(row.find('.arrack-input').val()) || 0;
                    beer = parseFloat(row.find('.beer-input').val()) || 0;
                    other = parseFloat(row.find('.other-input').val()) || 0;
                    serviceCharge = parseFloat(row.find('.service-charge-input').val()) || 0;
                } else {
                    // For system-generated rows, get values from cells
                    rooms = parseFloat(row.find('td:eq(2)').text()) || 0;
                    swimming = parseFloat(row.find('td:eq(3)').text()) || 0;
                    arrack = parseFloat(row.find('td:eq(4)').text()) || 0;
                    beer = parseFloat(row.find('td:eq(5)').text()) || 0;
                    other = parseFloat(row.find('td:eq(6)').text()) || 0;
                    serviceCharge = parseFloat(row.find('td:eq(7)').text()) || 0;
                }
                
                // Sum up category totals
                totalRooms += rooms;
                totalSwimming += swimming;
                totalArrack += arrack;
                totalBeer += beer;
                totalOther += other;
                totalServiceCharge += serviceCharge;
                
                // Calculate row total excluding service charge
                const rowTotal = rooms + swimming + arrack + beer + other;
                totalAmount += rowTotal;
                
                // Payment totals
                totalCash += parseFloat(row.find('.cash-input').val()) || 0;
                totalCard += parseFloat(row.find('.card-input').val()) || 0;
                totalBank += parseFloat(row.find('.bank-input').val()) || 0;
            });
            
            // Update footer totals with 2 decimal places
            $('#total-rooms').text(totalRooms.toFixed(2));
            $('#total-swimming').text(totalSwimming.toFixed(2));
            $('#total-arrack').text(totalArrack.toFixed(2));
            $('#total-beer').text(totalBeer.toFixed(2));
            $('#total-other').text(totalOther.toFixed(2));
            $('#total-service-charge').text(totalServiceCharge.toFixed(2));
            $('#total-amount').text(totalAmount.toFixed(2));
            $('#total-cash').text(totalCash.toFixed(2));
            $('#total-card').text(totalCard.toFixed(2));
            $('#total-bank').text(totalBank.toFixed(2));
        }

        // Function to update a single row's total
        function updateRowTotal(row) {
            if (row.hasClass('manual-row')) {
                const rooms = parseFloat(row.find('.rooms-input').val()) || 0;
                const swimming = parseFloat(row.find('.swimming-input').val()) || 0;
                const arrack = parseFloat(row.find('.arrack-input').val()) || 0;
                const beer = parseFloat(row.find('.beer-input').val()) || 0;
                const other = parseFloat(row.find('.other-input').val()) || 0;
                
                // Don't include service charge in the total calculation
                const total = rooms + swimming + arrack + beer + other;
                row.find('.total-input').val(total.toFixed(2));
            }
        }

        // Function to validate payment distribution in a row
        function updateRowPaymentDistribution(row) {
            const total = row.hasClass('manual-row') 
                ? parseFloat(row.find('.total-input').val()) || 0
                : parseFloat(row.find('td:eq(9)').text()) || 0;
                
            const cash = parseFloat(row.find('.cash-input').val()) || 0;
            const card = parseFloat(row.find('.card-input').val()) || 0;
            const bank = parseFloat(row.find('.bank-input').val()) || 0;
            
            const totalPayment = cash + card + bank;
            
            // If total payment exceeds the bill total, adjust
            if (totalPayment > total) {
                // For simplicity, adjust the last changed field
                const lastChanged = row.find('input:focus');
                if (lastChanged.length) {
                    const newValue = parseFloat(lastChanged.val()) - (totalPayment - total);
                    lastChanged.val(Math.max(0, newValue.toFixed(2)));
                }
            }
            
            // If payment is equal to total, mark as paid
            if (Math.abs(totalPayment - total) < 0.01 && totalPayment > 0) {
                row.find('.status-select').val('paid');
            }
        }
    });


    
</script>
<form id="verify-form" action="{{ route('report.daily-summary.toggle-verify') }}" method="POST" style="display:none;">
    @csrf
    <input type="hidden" name="bill_number" id="verify-bill-number">
    <input type="hidden" name="verified" id="verify-status">
    <input type="hidden" name="date" id="verify-date">
</form>

@endsection