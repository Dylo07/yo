<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daily Food Menu - {{ $date->format('F j, Y') }}</title>
    <style>
        @page { size: A4 portrait; margin: 5mm 6mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 7pt; line-height: 1.15; color: #000; background: #fff; }

        .page-header { text-align: center; margin-bottom: 2mm; padding-bottom: 1mm; border-bottom: 1.5pt solid #000; }
        .page-header h1 { font-size: 10pt; margin: 0; }
        .page-header .meta { font-size: 6pt; color: #555; margin-top: 0.5mm; }

        .booking-block { margin-bottom: 3mm; border: 0.75pt solid #000; page-break-inside: avoid; }

        .booking-head { display: flex; justify-content: space-between; align-items: center; padding: 1mm 2mm; background: #333; color: #fff; font-size: 7.5pt; font-weight: bold; }
        .booking-head .wedding-tag { background: #fff; color: #000; padding: 0.3mm 1.5mm; font-size: 6pt; font-weight: bold; margin-left: 1.5mm; }

        .info-bar { display: flex; flex-wrap: wrap; gap: 1mm 3mm; padding: 1mm 2mm; font-size: 6pt; background: #f5f5f5; border-bottom: 0.5pt solid #ccc; }
        .info-bar b { font-weight: bold; }

        .details-bar { padding: 0.8mm 2mm; font-size: 5.5pt; color: #444; border-bottom: 0.5pt solid #eee; }
        .details-bar b { font-weight: bold; font-size: 5.5pt; }

        .menu-tbl { width: 100%; border-collapse: collapse; }
        .menu-tbl th { background: #555; color: #fff; padding: 0.8mm 1.5mm; text-align: left; font-size: 6.5pt; font-weight: bold; }
        .menu-tbl td { border-bottom: 0.25pt solid #ddd; padding: 0.6mm 1.5mm; vertical-align: top; font-size: 6.5pt; line-height: 1.25; }
        .menu-tbl .n { width: 6mm; text-align: center; font-weight: bold; color: #666; }
        .menu-tbl .lbl { width: 22mm; font-weight: bold; background: #fafafa; white-space: nowrap; }
        .menu-tbl .tm { width: 14mm; text-align: center; font-size: 6pt; color: #555; }
        .menu-tbl .val { }
        .menu-tbl tr.empty-row { display: none; }

        .booking-foot { text-align: right; padding: 0.5mm 2mm; font-size: 5pt; color: #888; border-top: 0.25pt solid #ccc; }

        .page-footer { margin-top: 2mm; text-align: right; font-size: 5pt; color: #888; }

        .no-bookings { text-align: center; padding: 10mm; font-size: 9pt; color: #999; }

        @media print {
            body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
            .booking-block { page-break-inside: avoid; break-inside: avoid; }
            .booking-head { background: #333 !important; color: #fff !important; }
            .menu-tbl th { background: #555 !important; color: #fff !important; }
        }
    </style>
</head>
<body>
    <div class="page-header">
        <h1>DAILY FOOD MENU</h1>
        <div class="meta">{{ $date->format('l, F j, Y') }} &nbsp;|&nbsp; {{ $bookingsWithMenus->count() }} booking(s) &nbsp;|&nbsp; Generated: {{ now()->format('M j, H:i') }}</div>
    </div>

    @if($bookingsWithMenus->count() == 0)
        <div class="no-bookings">No bookings found for this date.</div>
    @else
        @foreach($bookingsWithMenus as $index => $item)
        @php
            $booking = $item['booking'];
            $menu = $item['menu'];
            $isWedding = $booking->function_type == 'Wedding';
        @endphp
        <div class="booking-block">
            <div class="booking-head">
                <span>
                    #{{ $booking->id }} — {{ $booking->function_type }}
                    @if($isWedding)<span class="wedding-tag">WEDDING</span>@endif
                </span>
                <span>{{ $booking->guest_count }} Guests | Rooms: {{ is_array($booking->room_numbers) ? implode(', ', $booking->room_numbers) : $booking->room_numbers }}</span>
            </div>

            <div class="info-bar">
                <span><b>In:</b> {{ $booking->start->format('M j, h:i A') }}</span>
                <span><b>Out:</b> {{ $booking->end ? $booking->end->format('M j, h:i A') : 'N/A' }}</span>
                @if($booking->name)<span><b>Desc:</b> {{ $booking->name }}</span>@endif
            </div>

            @if($booking->bites_details || $booking->other_details)
            <div class="details-bar">
                @if($booking->bites_details)<b>Bites:</b> {{ $booking->bites_details }} &nbsp; @endif
                @if($booking->other_details)<b>Other:</b> {{ $booking->other_details }}@endif
            </div>
            @endif

            @if($isWedding)
            @php
                $weddingItems = [
                    ['Welcome Drink', $menu->wedding_welcome_drink ?? ''],
                    ['Appetizer', $menu->wedding_appetizer ?? ''],
                    ['Shooters', $menu->wedding_shooters ?? ''],
                    ['Salad Bar', $menu->wedding_salad_bar ?? ''],
                    ['Salad Dressing', $menu->wedding_salad_dressing ?? ''],
                    ['Soup', $menu->wedding_soup ?? ''],
                    ['Bread Corner', $menu->wedding_bread_corner ?? ''],
                    ['Rice & Noodle', $menu->wedding_rice_noodle ?? ''],
                    ['Meat Items', $menu->wedding_meat_items ?? ''],
                    ['Seafood Items', $menu->wedding_seafood_items ?? ''],
                    ['Vegetables', $menu->wedding_vegetables ?? ''],
                    ['Condiments', $menu->wedding_condiments ?? ''],
                    ['Desserts', $menu->wedding_desserts ?? ''],
                    ['Beverages', $menu->wedding_beverages ?? ''],
                ];
                $wNum = 0;
            @endphp
            <table class="menu-tbl">
                <thead><tr><th class="n">#</th><th class="lbl">Item</th><th class="val">Menu Details</th></tr></thead>
                <tbody>
                    @foreach($weddingItems as $wi)
                        @if(!empty(trim($wi[1])))
                        @php $wNum++; @endphp
                        <tr><td class="n">{{ $wNum }}</td><td class="lbl">{{ $wi[0] }}</td><td class="val">{{ $wi[1] }}</td></tr>
                        @endif
                    @endforeach
                    @if($wNum === 0)
                        <tr><td colspan="3" style="text-align:center; padding:2mm; color:#999;">No menu items set</td></tr>
                    @endif
                </tbody>
            </table>

            @else
            @php
                $regularItems = [
                    ['Welcome Drink', '-', $menu->welcome_drink ?? ''],
                    ['Evening Snack', $menu->evening_snack_time ? $menu->getFormattedTime('evening_snack_time') : '', $menu->evening_snack ?? ''],
                    ['Dinner', $menu->dinner_time ? $menu->getFormattedTime('dinner_time') : '', $menu->dinner ?? ''],
                    ['Dessert (Dinner)', '', $menu->dessert_after_dinner ?? ''],
                    ['Bed Tea', $menu->bed_tea_time ? $menu->getFormattedTime('bed_tea_time') : '', $menu->bed_tea ?? ''],
                    ['Breakfast', $menu->breakfast_time ? $menu->getFormattedTime('breakfast_time') : '', $menu->breakfast ?? ''],
                    ['Dessert (Breakfast)', '', $menu->dessert_after_breakfast ?? ''],
                    ['Lunch', $menu->lunch_time ? $menu->getFormattedTime('lunch_time') : '', $menu->lunch ?? ''],
                    ['Dessert (Lunch)', '', $menu->dessert_after_lunch ?? ''],
                ];
                $rNum = 0;
            @endphp
            <table class="menu-tbl">
                <thead><tr><th class="n">#</th><th class="lbl">Meal</th><th class="tm">Time</th><th class="val">Menu Details</th></tr></thead>
                <tbody>
                    @foreach($regularItems as $ri)
                        @if(!empty(trim($ri[2])))
                        @php $rNum++; @endphp
                        <tr><td class="n">{{ $rNum }}</td><td class="lbl">{{ $ri[0] }}</td><td class="tm">{{ $ri[1] ?: '-' }}</td><td class="val">{{ $ri[2] }}</td></tr>
                        @endif
                    @endforeach
                    @if($rNum === 0)
                        <tr><td colspan="4" style="text-align:center; padding:2mm; color:#999;">No menu items set</td></tr>
                    @endif
                </tbody>
            </table>
            @endif

            <div class="booking-foot">Booking #{{ $booking->id }} &nbsp;|&nbsp; {{ $index + 1 }}/{{ $bookingsWithMenus->count() }}</div>
        </div>
        @endforeach
    @endif

    <div class="page-footer">{{ now()->format('Y-m-d H:i') }}</div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
