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
        body {
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
            font-family: Arial, sans-serif;
            color: #333;
        }
        #wrapper {
            max-width: 200%;
            margin: 0 auto;
        }
        .terms-conditions {
            margin-top: 10px;
        }
        .terms-conditions ol {
            padding-left: 20px;
            margin: 5px 0;
        }
        .terms-conditions ul {
            padding-left: 15px;
            margin: 2px 0;
        }
        .value-highlight {
            font-weight: bold;
            color: #d32f2f;
        }
        .signature-section {
            margin-top: 20px;
            display: flex;
            justify-content: space-between;
        }
        .signature-line {
            border-top: 1px solid #000;
            width: 45%;
            text-align: center;
            padding-top: 4px;
            font-size: 11px;
        }
        .receipt-title {
            font-size: 18px;
            color: #3c763d;
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            border-bottom: 2px solid #3c763d;
            padding-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        #recipt-header p {
            margin: 3px 0;
            text-align: center;
        }
        #recipt-header img {
            max-width: 120px;
            height: auto;
        }
        .header-info {
            text-align: center;
            margin: 5px 0;
        }
        .tb-sale-detail {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        .tb-sale-detail th, .tb-sale-detail td {
            border: 1px solid #ddd;
            padding: 6px;
        }
        .tb-sale-detail th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: left;
        }
        h3 {
            font-size: 14px;
            margin: 12px 0 6px 0;
            color: #3c763d;
            border-bottom: 1px solid #ddd;
            padding-bottom: 4px;
        }
        li {
            margin-bottom: 6px;
        }
        ul li {
            margin-bottom: 3px;
        }
        #buttons {
            margin-top: 15px;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            padding: 8px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
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
        #recipt-footer {
            text-align: center;
            margin-top: 15px;
            font-size: 11px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        #recipt-footer p {
            margin: 2px 0;
        }
        .order-info {
            background-color: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 15px;
            font-size: 11px;
        }
        .order-info p {
            margin: 3px 0;
        }
        .section-heading {
            font-weight: bold;
            font-size: 12px;
            margin-bottom: 4px;
            color: #3c763d;
        }
        .important-notice {
            background-color: #fcf8e3;
            border: 1px solid #faebcc;
            border-radius: 4px;
            padding: 8px;
            margin: 10px 0;
            color: #8a6d3b;
            font-size: 11px;
        }
        .total-row {
            font-weight: bold;
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="recipt-header">
            <p><img width="120px" src="{{asset('image/lg.png')}}" alt="Logo"></p>
            <p>Balawattala Road, Melsiripura, Kurunegala</p>
            <p>| Restaurant | Swimming Pool | Cottages | Bar |</p>
            <p>Tel: 037 2250 308 | 071 7152 955</p>
            <p>Invoice No: <strong>{{$sale->id}}</strong> &nbsp; Date: <strong>{{$sale->updated_at}}</strong></p>
        </div>
        <div id="recipt-body">
            <h2 class="receipt-title">Wedding Advance Payment Receipt</h2>
            
            <div class="order-info">
                <div class="section-heading">EVENT DETAILS:</div>
                <p><strong>Event Date:</strong> _______________________</p>
                <p><strong>Client Name:</strong> _______________________</p>
                <p><strong>Contact Number:</strong> _______________________</p>                
                <p><strong>Initial Guest Count:</strong> _______________________</p>
            </div>
            
            <table class="tb-sale-detail">
                <thead>
                    <tr>
                        <th>Menu</th>
                        <th>Qty</th>
                        <th>Price</th>
                        <th style="text-align:right;">Total</th>
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
            
            <div class="terms-conditions">
                <h3>Terms and Conditions:</h3>
                <ol>
                    <li><strong>Reservation & Payments:</strong> All payments should be completed 30 days before the function. The full amount must be paid on the event date. This advance payment is non-refundable in case of cancellation less than 90 days before the event.</li>
                    
                    <li><strong>Payment Schedule:</strong>
                        <ul>
                            <li>25% non-refundable deposit required to secure date and venue (current payment)</li>
                            <li>50% of estimated total due 90 days prior to wedding date</li>
                            <li>Remaining balance due 30 days prior to wedding date</li>
                            <li>Any additional charges incurred during the event to be settled on the day</li>
                        </ul>
                    </li>
                    
                    <li><strong>Guest Count Confirmation:</strong> The final guest count must be confirmed at least two (2) weeks prior to the function date. Otherwise, charges will be based on the initial guest count. Any increase in guest count is subject to availability and must be approved by management.</li>
                    
                    <li><strong>Child Policy:</strong> No child policy and the count will be taken based on plates & not the heads. Children under 5 years eat free, children aged 5-12 are charged at 50% of the adult price.</li>
                    
                    <li><strong>Function Duration:</strong> The Banquet Hall is provided for a maximum of seven (7) hours. Additional time will be charged at Rs. 25,000/- per hour. The venue must be vacated by the agreed end time.</li>
                    
                    <li><strong>Food & Beverage:</strong>
                        <ul>
                            <li><strong>Bites:</strong> Dry Bites & Mixtures can be brought from outside, cooked bites needed to buy from the hotel.</li>
                            <li><strong>Soft Drinks:</strong> All soft drinks must be purchased from the hotel.</li>
                            <li><strong>Hard Liquor:</strong> Outside liquor is subject to a corkage fee of Rs. 500 per bottle.</li>
                            <li><strong>Foods:</strong> Buffet service is available for a maximum of Two and a half hours. Foods are not allowed to be carried outside from the Hotel.</li>
                            <li><strong>Menu Changes:</strong> Menu items are subject to availability and seasonal changes. We reserve the right to make suitable substitutions if necessary.</li>
                            <li><strong>Special Dietary Requirements:</strong> Must be communicated at least 14 days prior to the event.</li>
                        </ul>
                    </li>
                    
                    <li><strong>Music & Entertainment:</strong> Music must be closed by 11:00 PM in night functions. All entertainment providers must be approved by management and comply with venue policies.</li>
                    
                    <li><strong>Liquor Service Bar:</strong> Must close two hours before the end of the function. Last call will be announced 30 minutes prior to bar closing.</li>
                    
                    <li><strong>Decorations & Setup:</strong>
                        <ul>
                            <li>All decorations must be approved by management</li>
                            <li>No nails, screws, staples, or adhesives are allowed on walls or fixtures</li>
                            <li>All decorations must be removed immediately following the event</li>
                            <li>Setup time must be scheduled in advance</li>
                        </ul>
                    </li>
                    
                    <li><strong>Damages:</strong> The patron is responsible and agrees to indemnify Soba Lanka Resort for all damages sustained to the hotel and grounds during an event by any of their employees, contractors, invitees, or guests. A security deposit may be required.</li>
                    
                    <li><strong>Cancellation Policy:</strong>
                        <ul>
                            <li>More than 180 days before the event: 50% of deposit refunded</li>
                            <li>90-180 days before the event: 25% of deposit refunded</li>
                            <li>Less than 90 days before the event: No refund</li>
                            <li>Date changes within 90 days of the event are subject to availability and a Rs. 10,000 rescheduling fee</li>
                        </ul>
                    </li>
                    
                    <li><strong>Photography & Publicity:</strong> Soba Lanka Resort reserves the right to use photographs or videos from the event for promotional purposes unless explicitly prohibited in writing by the client.</li>
                    
                    <li><strong>Force Majeure:</strong> Soba Lanka Resort shall not be liable for any failure to perform its obligations where such failure is a result of acts of nature, government restrictions, disasters, or other emergencies.</li>
                    
                    <li><strong>Service Charge:</strong> A service charge of at least Rs 5,000 shall be added to the total amount paid. This covers staff gratuities and administrative costs.</li>
                </ol>
            </div>
            
            <div class="signature-section" style="margin-top: 20px; display: flex; justify-content: space-between; width: 100%;">
    <div style="width: 45%; text-align: center;">
        <div class="signature-line" style="border-top: 1px solid #000; width: 100%; margin: 0 auto;">
            Customer Signature
        </div>
        <div style="text-align: left; font-size: 10px; margin-top: 5px;">
            Date: _________________
        </div>
    </div>
    <div style="width: 45%; text-align: center;">
        <div class="signature-line" style="border-top: 1px solid #000; width: 100%; margin: 0 auto;">
            For Soba Lanka Resort
        </div>
        <div style="text-align: left; font-size: 10px; margin-top: 5px;">
            Date: _________________
        </div>
    </div>
</div>
        
        <div id="recipt-footer">
            <p>ස්තූතීයි, නැවත එන්න !!</p>
            <p>THANK YOU, COME AGAIN !!</p>
        </div>
        
        <div id="buttons">
            <button class="btn btn-print" type="button" onclick="window.print(); return false;">
                Print
            </button>
            <a href="/cashier" style="width: 48%; text-decoration: none;">
                <button class="btn btn-back" style="width: 100%;">
                    Back to cashier
                </button>
            </a>
        </div>
    </div>
</body>
</html>