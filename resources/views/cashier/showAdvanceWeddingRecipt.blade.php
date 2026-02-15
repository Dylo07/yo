<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soba Lanka - Wedding Advance Payment Receipt - Sale ID: {{$sale->id}}</title>
    <link type="text/css" rel="stylesheet" href="{{asset('css/recipt.css')}}" media="all">
    <link type="text/css" rel="stylesheet" href="{{asset('css/no-print.css')}}" media="print">
    <style>
        /* Page Settings */
        @page {
            size: auto;
            margin: 5mm;
        }
        
        body {
            font-size: 10px;
            line-height: 1.3;
            margin: 0;
            padding: 0;
            font-family: Arial, Helvetica, sans-serif;
            color: #333;
            background-color: #fff;
        }
        
        #wrapper {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            padding: 5px;
            box-sizing: border-box;
        }
        
        /* For print media */
        @media print {
            body, html {
                width: 100%;
                margin: 0;
                padding: 0;
            }
            #wrapper {
                width: 100%;
                margin: 0;
                padding: 5px;
            }
            #buttons {
                display: none !important;
            }
        }
        
        /* Header Styling */
        #recipt-header {
            text-align: center;
            border-bottom: 1px solid #2d5e2d;
            padding-bottom: 3px;
            margin-bottom: 8px;
        }
        
        #recipt-header p {
            margin: 1px 0;
            text-align: center;
            font-size: 9px;
        }
        
        #recipt-header img {
            max-width: 65px;
            height: auto;
            margin: 0 auto;
            display: block;
        }
        
        /* Title Styling */
        .receipt-title {
            font-size: 13px;
            color: #2d5e2d;
            text-align: center;
            margin: 8px 0;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        /* Content Styling */
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 5px;
        }
        
        .section-title {
            background-color: #f5f5f5;
            padding: 3px 5px;
            font-weight: bold;
            color: #2d5e2d;
            font-size: 10px;
        }
        
        .detail-cell {
            padding: 2px 5px;
        }
        
        .form-field {
            border-bottom: 1px solid #ddd;
            height: 14px;
        }
        
        /* Event Details */
        .event-details {
            margin-bottom: 8px;
        }
        
        /* Menu Table */
        .menu-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0;
            font-size: 9px;
        }
        
        .menu-table th, .menu-table td {
            border: 1px solid #ddd;
            padding: 3px 5px;
        }
        
        .menu-table th {
            background-color: #f5f5f5;
            color: #333;
            font-weight: bold;
            text-align: left;
        }
        
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        
        /* Important Notice */
        .important-notice {
            background-color: #f9f9f9;
            border-left: 3px solid #2d5e2d;
            padding: 5px 8px;
            margin: 6px 0;
            font-style: italic;
            font-size: 9px;
        }
        
        /* Terms and Conditions */
        .terms-section {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9px;
            line-height: 1.3;
            color: #333;
            background-color: #fbfbfb;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 6px 8px;
            margin: 6px 0;
        }
        
        .terms-title {
            font-size: 11px;
            font-weight: bold;
            color: #2d5e2d;
            margin: 0 0 4px 0;
            padding-bottom: 3px;
            border-bottom: 1px solid #d8d8d8;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .terms-list {
            list-style-type: none;
            counter-reset: item;
            margin: 0;
            padding: 0;
        }
        
        .terms-list li {
            counter-increment: item;
            margin-bottom: 3px;
            padding-left: 20px;
            position: relative;
            text-align: justify;
        }
        
        .terms-list li:before {
            content: counter(item) ".";
            position: absolute;
            left: 0;
            top: 0;
            font-weight: bold;
            color: #2d5e2d;
        }
        
        .terms-list li strong {
            color: #2d5e2d;
        }
        
        /* Footer */
        #recipt-footer {
            text-align: center;
            margin-top: 5px;
            font-size: 9px;
            padding-top: 4px;
        }
        
        #recipt-footer p {
            margin: 1px 0;
        }
        
        /* Signature Section */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            width: 45%;
            text-align: center;
            padding-top: 2px;
            font-size: 9px;
        }
        
        /* Buttons */
        #buttons {
            margin-top: 8px;
            display: flex;
            justify-content: space-between;
        }
        
        .btn {
            padding: 4px 8px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 10px;
            width: 48%;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .btn-print {
            background-color: #f0ad4e;
            color: white;
        }
        
        .btn-back {
            background-color: #5cb85c;
            color: white;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="recipt-header">
            <img width="65px" src="{{asset('image/lg.png')}}" alt="Logo">
            <p>Balawattala Road, Melsiripura, Kurunegala</p>
            <p>| Restaurant | Swimming Pool | Cottages | Bar |</p>
            <p>Tel: 037 2250 308 | 071 7152 955</p>
            <p>Invoice No: <strong>{{$sale->id}}</strong> &nbsp; Date: <strong>{{$sale->updated_at}}</strong></p>
        </div>
        
        <h2 class="receipt-title">Wedding Advance Payment Receipt</h2>
        
        <!-- Event Details -->
        <table class="content-table event-details">
            <tr>
                <td colspan="4" class="section-title">EVENT DETAILS:</td>
            </tr>
            <tr>
                <td class="detail-cell" width="20%"><strong>Event Date:</strong></td>
                <td width="30%" class="form-field"></td>
                <td class="detail-cell" width="20%"><strong>Client Name:</strong></td>
                <td width="30%" class="form-field"></td>
            </tr>
            <tr>
                <td class="detail-cell"><strong>Contact Number:</strong></td>
                <td class="form-field"></td>
                <td class="detail-cell"><strong>Initial Guest Count:</strong></td>
                <td class="form-field"></td>
            </tr>
        </table>
        
        <!-- Menu Details -->
        <table class="menu-table">
            <thead>
                <tr>
                    <th>Menu</th>
                    <th width="10%">Qty</th>
                    <th width="15%">Price</th>
                    <th width="20%" style="text-align:right;">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($saleDetails as $saleDetail)
                <tr>
                    <td>{{$saleDetail->menu_name}}</td>
                    <td>{{$saleDetail->quantity}}</td>
                    <td>{{$saleDetail->menu_price}}</td>
                    <td style="text-align:right;">{{$saleDetail->menu_price*$saleDetail->quantity}}</td>
                </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" style="text-align:right;"><strong>Total Amount:</strong></td>
                    <td style="text-align:right;">Rs {{number_format($sale->total_price, 2)}}</td>
                </tr>
                <tr>
                    <td colspan="3" style="text-align:right;"><strong>Advance Payment:</strong></td>
                    <td style="text-align:right;">Rs {{number_format($sale->total_price, 2)}}</td>
                </tr>
            </tbody>
        </table>

        <div class="important-notice">
            <strong>IMPORTANT:</strong> This advance payment secures your booking for the selected date and venue. Please read all terms and conditions carefully.
        </div>
        
        <!-- Enhanced Terms and Conditions -->
        <div class="terms-section">
            <div class="terms-title">Terms and Conditions</div>
            <ol class="terms-list">
                <li><strong>Reservation & Payments:</strong> All payments should be completed 30 days before the function. The full amount must be paid on the event date. This advance payment is non-refundable in case of cancellation less than 90 days before the event.</li>
                
                <li><strong>Payment Schedule:</strong> 25% non-refundable deposit to secure date (current payment); 50% due 90 days prior; remaining due 30 days prior; additional charges settled on event day.</li>
                
                <li><strong>Guest Count:</strong> Final count must be confirmed 2 weeks prior. Otherwise, charges based on initial count. Increases subject to availability.</li>
                
                <li><strong>Child Policy:</strong> Count based on plates.</li>
                
                <li><strong>Function Duration:</strong> Maximum 7 hours. Additional time: Rs. 25,000/hour.</li>
                
                <li><strong>Food & Beverage:</strong> Dry bites can be outside; cooked bites, soft drinks from hotel; outside liquor: Rs. 500/bottle corkage; buffet: 2.5 hours max; no takeaway; special diets: 14 days notice. <strong>Buffet food cannot be taken outside.</strong></li>
                
                <li><strong>Music & Entertainment:</strong> Must close by 11:00 PM. All providers must be approved.</li>
                
                <li><strong>Liquor Service:</strong> Closes 2 hours before end of function.</li>
                
                <li><strong>Decorations:</strong> All must be approved; no adhesives/nails; all removed post-event; setup scheduled in advance.</li>
                
                <li><strong>Damages:</strong> Client responsible for all damages by guests or contractors.</li>
                
                <li><strong>Cancellation:</strong> >180 days: 50% refund; 90-180 days: 25% refund; <90 days: no refund; date changes: Rs. 10,000 fee.</li>
                
                <li><strong>Photography:</strong> Resort may use event photos unless prohibited in writing.</li>
                
                <li><strong>Force Majeure:</strong> No liability for events beyond control.</li>
                
                <li><strong>Service Charge:</strong> Minimum Rs. 5,000 for staff gratuities and administration.</li>
            </ol>
        </div>
        
        <div id="recipt-footer">
            <p>ස්තූතීයි, නැවත එන්න !!</p>
            <p>THANK YOU, COME AGAIN !!</p>
        </div>
        
        <div class="signature-section">
            <div class="signature-line">Customer Signature</div>
            <div class="signature-line">For Soba Lanka Resort</div>
        </div>
        
        <div id="buttons">
            <button class="btn btn-print" type="button" onclick="window.print(); return false;">
                Print Receipt
            </button>
            <a href="/cashier" style="width: 48%; text-decoration: none;">
                <button class="btn btn-back" style="width: 100%;">
                    Back to Cashier
                </button>
            </a>
        </div>

        <div id="booking-actions" style="margin-top:15px; border:2px solid #007bff; border-radius:8px; padding:12px; background:#f0f7ff;">
            <h3 style="font-size:12px; color:#007bff; margin:0 0 10px; text-align:center; font-weight:bold; text-transform:uppercase; letter-spacing:0.5px;">
                Booking Options
            </h3>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button id="btnExistingBooking" onclick="openExistingBookingModal()" style="flex:1; min-width:140px; padding:10px 8px; background:linear-gradient(135deg,#28a745,#20c997); color:#fff; border:none; border-radius:6px; font-size:10px; font-weight:bold; cursor:pointer; text-transform:uppercase;">
                    + Add to Existing Booking
                </button>
                <button id="btnNewBooking" onclick="goToNewBooking()" style="flex:1; min-width:140px; padding:10px 8px; background:linear-gradient(135deg,#007bff,#6610f2); color:#fff; border:none; border-radius:6px; font-size:10px; font-weight:bold; cursor:pointer; text-transform:uppercase;">
                    New Booking
                </button>
            </div>
            <p style="font-size:8px; color:#666; text-align:center; margin:6px 0 0;">Bill #{{$sale->id}} | Rs {{ number_format($sale->total_price, 2) }}</p>
        </div>
    </div>

    <div id="bookingModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; overflow-y:auto;">
        <div style="max-width:600px; margin:30px auto; background:#fff; border-radius:12px; box-shadow:0 10px 40px rgba(0,0,0,0.3); overflow:hidden;">
            <div style="background:linear-gradient(135deg,#28a745,#20c997); padding:15px 20px; color:#fff;">
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <h4 style="margin:0; font-size:14px;">Add Payment to Existing Booking</h4>
                    <button onclick="closeBookingModal()" style="background:none; border:none; color:#fff; font-size:20px; cursor:pointer;">&times;</button>
                </div>
                <p style="margin:4px 0 0; font-size:10px; opacity:0.9;">Bill #{{$sale->id}} - Rs {{ number_format($sale->total_price, 2) }}</p>
            </div>
            <div style="padding:15px 20px;">
                <div style="position:relative; margin-bottom:12px;">
                    <input type="text" id="bookingSearch" placeholder="Search by name, contact, function type, or booking ID..."
                           style="width:100%; padding:10px 12px; border:2px solid #ddd; border-radius:8px; font-size:12px; box-sizing:border-box;"
                           oninput="searchBookings(this.value)">
                </div>
                <div id="bookingResults" style="max-height:350px; overflow-y:auto;">
                    <p style="text-align:center; color:#999; font-size:11px; padding:20px;">Type to search for bookings...</p>
                </div>
                <div id="paymentMethodSection" style="display:none; margin-top:12px; padding-top:12px; border-top:2px solid #eee;">
                    <label style="font-size:11px; font-weight:bold; color:#333; display:block; margin-bottom:6px;">Payment Method:</label>
                    <div style="display:flex; gap:8px;">
                        <label style="flex:1; display:flex; align-items:center; gap:4px; padding:8px; border:2px solid #ddd; border-radius:6px; cursor:pointer; font-size:11px;">
                            <input type="radio" name="bookingPayMethod" value="cash" checked> Cash
                        </label>
                        <label style="flex:1; display:flex; align-items:center; gap:4px; padding:8px; border:2px solid #ddd; border-radius:6px; cursor:pointer; font-size:11px;">
                            <input type="radio" name="bookingPayMethod" value="online"> Online
                        </label>
                    </div>
                </div>
            </div>
            <div id="modalFooter" style="display:none; padding:12px 20px; background:#f8f9fa; border-top:1px solid #eee;">
                <button id="confirmAddPayment" onclick="confirmAddToBooking()"
                        style="width:100%; padding:10px; background:#28a745; color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:bold; cursor:pointer;">
                    Confirm & Add Payment
                </button>
            </div>
        </div>
    </div>

    <div id="actionMessage" style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:10000; padding:12px 24px; border-radius:8px; font-size:12px; font-weight:bold; box-shadow:0 4px 15px rgba(0,0,0,0.2); max-width:90%;"></div>

    <script>
        var saleId = {{ $sale->id }};
        var saleAmount = {{ $sale->total_price }};
        var saleDate = '{{ date("Y-m-d", strtotime($sale->updated_at)) }}';
        var selectedBookingId = null;
        var searchTimeout = null;

        function disableBookingButtons() {
            var btn1 = document.getElementById('btnExistingBooking');
            var btn2 = document.getElementById('btnNewBooking');
            if (btn1) { btn1.disabled = true; btn1.style.opacity = '0.5'; btn1.style.cursor = 'not-allowed'; }
            if (btn2) { btn2.disabled = true; btn2.style.opacity = '0.5'; btn2.style.cursor = 'not-allowed'; }
        }

        function enableBookingButtons() {
            var btn1 = document.getElementById('btnExistingBooking');
            var btn2 = document.getElementById('btnNewBooking');
            if (btn1) { btn1.disabled = false; btn1.style.opacity = '1'; btn1.style.cursor = 'pointer'; }
            if (btn2) { btn2.disabled = false; btn2.style.opacity = '1'; btn2.style.cursor = 'pointer'; }
        }

        function openExistingBookingModal() {
            disableBookingButtons();
            document.getElementById('bookingModal').style.display = 'block';
            document.getElementById('bookingSearch').value = '';
            document.getElementById('bookingResults').innerHTML = '<p style="text-align:center; color:#999; font-size:11px; padding:20px;">Type to search for bookings...</p>';
            document.getElementById('paymentMethodSection').style.display = 'none';
            document.getElementById('modalFooter').style.display = 'none';
            selectedBookingId = null;
            setTimeout(function() { document.getElementById('bookingSearch').focus(); }, 100);
        }

        function closeBookingModal() {
            document.getElementById('bookingModal').style.display = 'none';
            enableBookingButtons();
        }

        function goToNewBooking() {
            disableBookingButtons();
            var params = new URLSearchParams({
                bill_number: saleId,
                advance_payment: saleAmount,
                advance_date: saleDate,
                from_receipt: 1
            });
            window.location.href = '/calendar?' + params.toString();
        }

        function searchBookings(query) {
            clearTimeout(searchTimeout);
            var resultsDiv = document.getElementById('bookingResults');

            if (query.length < 1) {
                resultsDiv.innerHTML = '<p style="text-align:center; color:#999; font-size:11px; padding:20px;">Type to search for bookings...</p>';
                document.getElementById('paymentMethodSection').style.display = 'none';
                document.getElementById('modalFooter').style.display = 'none';
                selectedBookingId = null;
                return;
            }

            resultsDiv.innerHTML = '<p style="text-align:center; color:#999; font-size:11px; padding:20px;">Searching...</p>';

            searchTimeout = setTimeout(function() {
                fetch('/bookings/search?q=' + encodeURIComponent(query), {
                    headers: { 'Accept': 'application/json' }
                })
                .then(function(r) { return r.json(); })
                .then(function(bookings) {
                    if (bookings.length === 0) {
                        resultsDiv.innerHTML = '<p style="text-align:center; color:#999; font-size:11px; padding:20px;">No bookings found.</p>';
                        return;
                    }
                    var html = '';
                    bookings.forEach(function(b) {
                        html += '<div class="booking-result" data-id="' + b.id + '" onclick="selectBooking(' + b.id + ', this)" style="padding:10px 12px; border:2px solid #eee; border-radius:8px; margin-bottom:8px; cursor:pointer; transition:all 0.2s;">';
                        html += '<div style="display:flex; justify-content:space-between; align-items:center;">';
                        html += '<div>';
                        html += '<strong style="font-size:12px; color:#333;">#' + b.id + ' - ' + b.function_type + '</strong>';
                        html += '<div style="font-size:10px; color:#666; margin-top:2px;">' + (b.name || 'N/A') + '</div>';
                        html += '</div>';
                        html += '<div style="text-align:right;">';
                        html += '<div style="font-size:10px; color:#28a745; font-weight:bold;">Rs ' + parseFloat(b.total_paid).toLocaleString(undefined, {minimumFractionDigits:2}) + '</div>';
                        html += '<div style="font-size:9px; color:#999;">' + b.payment_count + ' payment(s)</div>';
                        html += '</div>';
                        html += '</div>';
                        html += '<div style="font-size:9px; color:#888; margin-top:4px;">Tel: ' + b.contact_number + ' | Guests: ' + (b.guest_count || 'N/A') + ' | ' + b.start + '</div>';
                        html += '</div>';
                    });
                    resultsDiv.innerHTML = html;
                })
                .catch(function(err) {
                    resultsDiv.innerHTML = '<p style="text-align:center; color:#dc3545; font-size:11px; padding:20px;">Error searching bookings.</p>';
                });
            }, 300);
        }

        function selectBooking(bookingId, el) {
            selectedBookingId = bookingId;
            var allResults = document.querySelectorAll('.booking-result');
            allResults.forEach(function(r) {
                r.style.borderColor = '#eee';
                r.style.background = '#fff';
            });
            el.style.borderColor = '#28a745';
            el.style.background = '#f0fff4';
            document.getElementById('paymentMethodSection').style.display = 'block';
            document.getElementById('modalFooter').style.display = 'block';
        }

        function confirmAddToBooking() {
            if (!selectedBookingId) return;

            var btn = document.getElementById('confirmAddPayment');
            btn.disabled = true;
            btn.textContent = 'Adding payment...';

            var method = document.querySelector('input[name="bookingPayMethod"]:checked').value;
            var csrfToken = '{{ csrf_token() }}';

            fetch('/bookings/add-payment-from-receipt', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    booking_id: selectedBookingId,
                    amount: saleAmount,
                    bill_number: String(saleId),
                    payment_date: saleDate,
                    payment_method: method
                })
            })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    closeBookingModal();
                    showMessage(data.message, 'success');
                } else {
                    showMessage(data.error || 'Failed to add payment.', 'error');
                }
                btn.disabled = false;
                btn.textContent = 'Confirm & Add Payment';
            })
            .catch(function(err) {
                showMessage('Network error. Please try again.', 'error');
                btn.disabled = false;
                btn.textContent = 'Confirm & Add Payment';
            });
        }

        function showMessage(text, type) {
            var msgDiv = document.getElementById('actionMessage');
            msgDiv.textContent = text;
            msgDiv.style.display = 'block';
            msgDiv.style.background = type === 'success' ? '#28a745' : '#dc3545';
            msgDiv.style.color = '#fff';
            setTimeout(function() { msgDiv.style.display = 'none'; }, 5000);
        }
    </script>
</body>
</html>