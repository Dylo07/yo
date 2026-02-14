@extends('layouts.app')

@section('content')
  <div class="container">
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
            <li class="breadcrumb-item active" aria-current="page">Result</li>
          </ol>
        </nav>
      </div>
    </div>
    <div class="row">
        <div class="col-md-12">
          @if($sales->count() > 0)
            <div class="alert alert-success" role="alert">
              <p>The Total Amount of Sale from {{$dateStart}} to {{$dateEnd}} is Rs {{number_format($totalSale, 2)}}  </p>
              <p>The Total Amount of S/C {{$dateStart}} to {{$dateEnd}} is Rs {{number_format($serviceCharge, 2)}}  </p>
              <p>Total Result: {{$sales->total()}}</p>
            </div>
            <table class="table">
              <thead>
                <tr class="bg-primary text-light">
                  <th scope="col">#</th>
                  <th scope="col">Receipt ID</th>
                  <th scope="col">Date Time</th>
                  <th scope="col">Table</th>
                  <th scope="col">Staff</th>
                  <th scope="col">Total Amount</th>
                </tr>
              </thead>
              <tbody>
                @php 
                  $countSale = ($sales->currentPage() - 1) * $sales->perPage() + 1;
                @endphp 
                @foreach($sales as $sale)
                  <tr class="{{ $sale->sale_status === 'cancelled' ? 'bg-secondary text-light' : 'bg-primary text-light' }}" id="sale-row-{{$sale->id}}">
                    <td>{{$countSale++}}</td>
                    <td>{{$sale->id}} @if($sale->sale_status === 'cancelled')<span class="badge badge-danger">CANCELLED</span>@endif</td>
                    <td>{{date("m/d/Y H:i:s", strtotime($sale->updated_at))}}</td>
                    <td>{{$sale->table_name}}</td>
                    <td>{{$sale->user_name}}</td>
                    <td class="sale-total-{{$sale->id}}">{{$sale->total_price}}</td>
                    <td>{{$sale->total_recieved}}</td>
                    <td>
                      {{$sale->change}}
                      @if(Auth::user() && Auth::user()->role === 'admin' && $sale->sale_status !== 'cancelled')
                        <button class="btn btn-danger btn-sm ml-2 btn-cancel-bill" data-sale-id="{{$sale->id}}" title="Cancel Bill & Restore Stock">
                          <i class="fa fa-ban"></i> Cancel Bill
                        </button>
                      @endif
                    </td>
                  </tr>
                  <tr >
                    <th></th>
                    <th>Menu ID</th>
                    <th>Menu</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Updated Time</th>
                    <th>@if(Auth::user() && Auth::user()->role === 'admin') Action @endif</th>
                  </tr>
                
                  @foreach($sale->saleDetails as $saleDetail)
                    <tr id="detail-row-{{$saleDetail->id}}">
                      <td></td>
                      <td>{{$saleDetail->menu_id}}</td>
                      <td>{{$saleDetail->menu_name}}</td>
                      <td>{{$saleDetail->quantity}}</td>
                      <td>{{$saleDetail->menu_price}}</td>
                      <td>{{$saleDetail->menu_price * $saleDetail->quantity}}</td>
                      <td>{{$saleDetail->created_at ? $saleDetail->created_at->format('d/m/Y H:i:s') : 'N/A'}}</td>
                      <td>
                        @if(Auth::user() && Auth::user()->role === 'admin' && $sale->sale_status !== 'cancelled')
                          <button class="btn btn-outline-danger btn-sm btn-void-item" data-detail-id="{{$saleDetail->id}}" data-menu-name="{{$saleDetail->menu_name}}" data-sale-id="{{$sale->id}}" title="Void Item & Restore Stock">
                            <i class="fa fa-times"></i> Void
                          </button>
                        @endif
                      </td>
                    </tr>
                  @endforeach
                @endforeach

                    <tr class="bg-dark text-light">
                      <th colspan ="8" class="text-center">Summary</th>
                    </tr>

                    <tr>
                      <th colspan ="2">Menu Id</th>
                      <th colspan ="3">Menu</th>
                      
                      <th colspan ="3">Quantity</th>
                        
                    </tr>
                    @php 
                      $CategoryNew='';
                    @endphp
                    
                    @foreach($summarySales as $sale)
                    @if ($CategoryNew != $sale->name)
                      <tr>
                      <td colspan ="8" align="center"><b>{{$sale->name}}</b></td>
                      </tr>
                    @endif
                    
                    @php 
                      $CategoryNew= $sale->name;
                    @endphp
  
                    <tr>
                      <td colspan ="2">{{$sale->menu_id}}</td>
                      <td colspan ="3">{{$sale->menu_name}}</td>
                      <td colspan ="3">{{$sale->qty_sum}}</td>
                        
                    </tr>
                    @endforeach
              </tbody>
            </table>
   
            {{$sales->appends($_GET)->links()}}

            

<div id="buttons">
            <a href="/export/salereport">
            <button class="btn btn-back">
                Show Report
            </button>
            


            
          @else
            <div class="alert alert-danger" role="alert">
              There is no Sale Report
            </div>
          @endif
        </div>
    </div>
  </div>

<!-- Reason Modal -->
<div class="modal fade" id="reasonModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="reasonModalTitle">Reason Required</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <p id="reasonModalDesc" class="mb-2"></p>
        <div class="form-group">
          <label for="reasonInput"><strong>Reason:</strong></label>
          <textarea id="reasonInput" class="form-control" rows="3" placeholder="Enter reason (e.g., wrong bill, duplicate, customer request)" required></textarea>
        </div>
        <div class="alert alert-info small">
          <i class="fa fa-info-circle"></i> Stock items will be automatically restored to kitchen inventory.
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="reasonConfirmBtn">Confirm</button>
      </div>
    </div>
  </div>
</div>

<!-- Result Modal -->
<div class="modal fade" id="resultModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header" id="resultModalHeader">
        <h5 class="modal-title" id="resultModalTitle">Result</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body" id="resultModalBody"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

@endsection
<script src="https://code.jquery.com/jquery-3.4.1.js"></script>
		<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.22.2/moment.min.js"></script>
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
		<script src="https://code.jquery.com/jquery-3.4.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.1.2/js/tempusdominus-bootstrap-4.js"></script>
        <script type="text/javascript">
        $(document).ready(function() {
            var pendingAction = null;

            // Cancel Bill button click
            $(document).on('click', '.btn-cancel-bill', function() {
                var saleId = $(this).data('sale-id');
                $('#reasonModalTitle').text('Cancel Bill #' + saleId);
                $('#reasonModalDesc').html('This will <strong>cancel the entire bill</strong> and <strong>restore all stock items</strong> that were deducted. This action cannot be undone.');
                $('#reasonInput').val('');
                pendingAction = { type: 'cancel-bill', sale_id: saleId };
                $('#reasonModal').modal('show');
            });

            // Void Item button click
            $(document).on('click', '.btn-void-item', function() {
                var detailId = $(this).data('detail-id');
                var menuName = $(this).data('menu-name');
                var saleId = $(this).data('sale-id');
                $('#reasonModalTitle').text('Void Item: ' + menuName);
                $('#reasonModalDesc').html('This will <strong>remove "' + menuName + '"</strong> from Bill #' + saleId + ' and <strong>restore its stock items</strong>. This action cannot be undone.');
                $('#reasonInput').val('');
                pendingAction = { type: 'void-item', sale_detail_id: detailId, sale_id: saleId };
                $('#reasonModal').modal('show');
            });

            // Confirm button in reason modal
            $('#reasonConfirmBtn').click(function() {
                var reason = $('#reasonInput').val().trim();
                if (!reason) {
                    alert('Please enter a reason.');
                    return;
                }
                if (!pendingAction) return;

                var btn = $(this);
                btn.prop('disabled', true).text('Processing...');

                if (pendingAction.type === 'cancel-bill') {
                    $.ajax({
                        type: 'POST',
                        url: '/report/cancel-bill',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sale_id: pendingAction.sale_id,
                            reason: reason
                        },
                        success: function(data) {
                            $('#reasonModal').modal('hide');
                            showResult(true, data.message, data.restored_items);
                            // Update the UI - mark bill as cancelled
                            var row = $('#sale-row-' + pendingAction.sale_id);
                            row.removeClass('bg-primary').addClass('bg-secondary');
                            row.find('td:eq(1)').append(' <span class="badge badge-danger">CANCELLED</span>');
                            row.find('.btn-cancel-bill').remove();
                            // Remove void buttons for this sale's items
                            $('[data-sale-id="' + pendingAction.sale_id + '"].btn-void-item').remove();
                        },
                        error: function(xhr) {
                            $('#reasonModal').modal('hide');
                            var msg = 'Failed to cancel bill.';
                            if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                            showResult(false, msg, []);
                        },
                        complete: function() {
                            btn.prop('disabled', false).text('Confirm');
                            pendingAction = null;
                        }
                    });
                } else if (pendingAction.type === 'void-item') {
                    $.ajax({
                        type: 'POST',
                        url: '/report/void-item',
                        data: {
                            _token: '{{ csrf_token() }}',
                            sale_detail_id: pendingAction.sale_detail_id,
                            reason: reason
                        },
                        success: function(data) {
                            $('#reasonModal').modal('hide');
                            showResult(true, data.message, data.restored_items);
                            // Remove the voided item row
                            $('#detail-row-' + pendingAction.sale_detail_id).fadeOut(300, function() { $(this).remove(); });
                            // Update sale total
                            if (data.new_total !== undefined) {
                                $('.sale-total-' + pendingAction.sale_id).text(data.new_total);
                            }
                            // If sale is now cancelled (no items left)
                            if (data.sale_cancelled) {
                                var row = $('#sale-row-' + pendingAction.sale_id);
                                row.removeClass('bg-primary').addClass('bg-secondary');
                                row.find('td:eq(1)').append(' <span class="badge badge-danger">CANCELLED</span>');
                                row.find('.btn-cancel-bill').remove();
                            }
                        },
                        error: function(xhr) {
                            $('#reasonModal').modal('hide');
                            var msg = 'Failed to void item.';
                            if (xhr.responseJSON && xhr.responseJSON.error) msg = xhr.responseJSON.error;
                            showResult(false, msg, []);
                        },
                        complete: function() {
                            btn.prop('disabled', false).text('Confirm');
                            pendingAction = null;
                        }
                    });
                }
            });

            function showResult(success, message, restoredItems) {
                var header = $('#resultModalHeader');
                header.removeClass('bg-success bg-danger text-white');
                if (success) {
                    header.addClass('bg-success text-white');
                    $('#resultModalTitle').text('Success');
                } else {
                    header.addClass('bg-danger text-white');
                    $('#resultModalTitle').text('Error');
                }
                var body = '<p>' + message + '</p>';
                if (restoredItems && restoredItems.length > 0) {
                    body += '<div class="alert alert-success small"><strong>Stock Restored:</strong><ul class="mb-0 mt-1">';
                    restoredItems.forEach(function(item) {
                        body += '<li>' + item + '</li>';
                    });
                    body += '</ul></div>';
                }
                $('#resultModalBody').html(body);
                $('#resultModal').modal('show');
            }
        });
        </script>
         