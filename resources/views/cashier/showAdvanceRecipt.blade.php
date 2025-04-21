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
        body {
            font-size: 12px;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
            font-family: Arial, sans-serif;
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
            margin-top: 15px;
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
            font-size: 16px;
            color: #3c763d;
            text-align: center;
            margin: 10px 0;
            font-weight: bold;
            border-bottom: 1px solid #3c763d;
            padding-bottom: 5px;
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
        }
        .tb-sale-detail th, .tb-sale-detail td {
            border: 1px solid #ddd;
            padding: 4px;
        }
        .tb-sale-detail th {
            background-color: #f2f2f2;
        }
        h3 {
            font-size: 13px;
            margin: 8px 0 4px 0;
        }
        li {
            margin-bottom: 4px;
        }
        ul li {
            margin-bottom: 2px;
        }
        #buttons {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
        }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            width: 48%;
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
            margin-top: 10px;
            font-size: 11px;
        }
        #recipt-footer p {
            margin: 2px 0;
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
            <h2 class="receipt-title">ADVANCE PAYMENT RECEIPT</h2>
            
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
                </tbody>
            </table>
            
            <div class="terms-conditions">
                <h3>Terms and Conditions:</h3>
                <ol>
                    <li><strong>Payment:</strong> Advance payment secures booking. Balance due on event day. Cancellations within 7 days forfeit advance payment.</li>
                    
                    <li><strong>Guest Count:</strong> Final count must be confirmed 7 days before event. If not provided, billing based on initial count. Decreases after deadline still billed at initial count.</li>
                    
                    <li><strong>Food & Beverage:</strong>
                        <ul>
                            <li><strong>Bites:</strong> Must be purchased from hotel. Outside food not permitted.</li>
                            <li><strong>Soft Drinks:</strong> Must be purchased from hotel. Corkage fees apply otherwise.</li>
                            <li><strong>Hard Liquor:</strong> Must be purchased from hotel. Outside liquor subject to corkage fees.</li>
                            <li><strong>Foods:</strong> Buffet service limited to two hours. Extensions charged extra.</li>
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
                    
                    <li><strong>Damages:</strong> Customer responsible for any damages caused during event. Security deposit may be required.</li>
                    
                    <li><strong>Service Charge:</strong> Minimum Rs. 5,000/- applies to total amount.</li>
                    
                    <li><strong>Force Majeure:</strong> Resort not liable for failures due to acts of nature, government restrictions, or emergencies.</li>
                    
                    <li><strong>Liability:</strong> Hotel's liability limited to the amount paid for the event.</li>
                    
                    <li><strong>Photography:</strong> Resort may use event photos for promotion unless prohibited in writing.</li>
                </ol>
            </div>
            
            <div class="signature-section">
                <div class="signature-line">Customer Signature</div>
                <div class="signature-line">For Soba Lanka Resort</div>
            </div>
        </div>
        
        <div id="recipt-footer">
            <p> ස්තූතීයි, නැවත එන්න !!</p>
            <p> THANK YOU, COME AGAIN !!</p>
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