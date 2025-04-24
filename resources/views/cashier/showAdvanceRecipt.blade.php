<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Soba Lanka - Advance Payment Receipt - Sale ID: {{$sale->id}}</title>
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
        
        /* Menu Table */
        .menu-table {
            width: 100%;
            border-collapse: collapse;
            margin: 5px 0 10px 0;
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
        
        .terms-list li ul {
            list-style-type: none;
            padding-left: 5px;
            margin: 2px 0;
        }
        
        .terms-list li ul li {
            counter-increment: none;
            padding-left: 14px;
            margin-bottom: 2px;
            position: relative;
        }
        
        .terms-list li ul li:before {
            content: "•";
            position: absolute;
            left: 0;
            top: 0;
            font-weight: bold;
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
        
        /* Total Amount */
        .total-amount {
            text-align: right;
            margin-top: 5px;
            font-weight: bold;
            font-size: 10px;
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
        
        <h2 class="receipt-title">Advance Payment Receipt</h2>
        
        <!-- Menu Details -->
        <table class="menu-table">
            <thead>
                <tr>
                    <th width="50%">Menu</th>
                    <th width="15%">Qty</th>
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
            </tbody>
        </table>
        
        <div class="total-amount">
            Total Amount: Rs 
            @php
                $total = 0;
                foreach($saleDetails as $saleDetail) {
                    $total += $saleDetail->menu_price * $saleDetail->quantity;
                }
                echo number_format($total, 2);
            @endphp
        </div>
        
        <!-- Enhanced Terms and Conditions -->
        <div class="terms-section">
            <div class="terms-title">Terms and Conditions</div>
            <ol class="terms-list">
                <li><strong>Payment:</strong> Advance payment secures booking. Balance due on event day. </li>
                
                <li><strong>Guest Count:</strong> Final count must be confirmed 5 days before event. If not provided, billing based on initial count. Decreases after deadline still billed at initial count.</li>
                <li><strong>Child Policy:</strong> Children between the ages of 5 and 12 are entitled to a discounted rate. Children under the age of 5 may dine free of charge; however, a separate buffet plate will not be provided.</li>
                <li><strong>Food & Beverage:</strong>
                    <ul>
                        <li><strong>Bites:</strong> Must be purchased from hotel. Outside food not permitted.</li>
                        <li><strong>Soft Drinks:</strong> Must be purchased from hotel. Corkage fees apply otherwise.</li>
                        <li><strong>Hard Liquor:</strong> Must be purchased from hotel. Outside liquor subject to corkage fees.</li>
                        <li><strong>Foods:</strong> Buffet service is available for a maximum of two hours. For evening functions, the buffet will close at 10:00 PM. Any extensions will incur additional charges.</li>
                        <li><strong>Menu:</strong> Items subject to availability. Substitutions may be made if necessary.</li>
                    </ul>
                </li>
                
                <li><strong>Venue:</strong>
                    <ul>
                        <li><strong>Time:</strong> Available only for booked duration. Overtime charged extra.</li>
                        <li><strong>Decoration:</strong> Must be pre-approved. No damaging materials allowed.</li>
                        <li><strong>Noise:</strong> Must comply with regulations. Volume adjustable at management's discretion.</li>
                    </ul>
                </li>
                
                <li><strong>Damages:</strong> Customer responsible for any damages. Security deposit may be required.</li>
                
                <li><strong>Service Charge:</strong> Minimum Rs. 5,000/- applies to total amount.</li>
                
                <li><strong>Force Majeure:</strong> Resort not liable for failures due to acts of nature, government restrictions, or emergencies.</li>
                
                <li><strong>Liability:</strong> Hotel's liability limited to the amount paid for the event.</li>
                
                <li><strong>Photography:</strong> Resort may use event photos for promotion unless prohibited in writing.</li>
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