<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
@extends('layouts.app')

@section('content')
<div class="container">
  <div class="row" id="table-detail"></div>
  <div class="row justify-content-center py5">
    <div class="col-md-5">
      <button tabindex="-2" class="btn btn-primary btn-block" id="btn-show-tables">View All Tables</button>
      <div id="selected-table"></div>
      <div id="order-detail"></div>
    </div>
    <div class="col-md-7">
      <nav>
        <div class="nav nav-tabs" id="nav-tab" role="tablist">
          @foreach($categories as $category)
            <a class="nav-item nav-link btn-outline-success" data-id="{{ $category->id }}" data-toggle="tab">
              {{ $category->name }}
            </a>
          @endforeach
        </div>
      </nav>
      <br>
      <div class="row search-sec">
        <div class="col-md-6"></div>
        <div class="col-md-6 pull-right">
          <input type="text" class="form-control" id="searchkeyword" placeholder="search menu" name="search">
        </div>
      </div>
      <div id="list-menu" class="row mt-2"></div>
    </div>
  </div>
</div>

<!-- Include only one version of jQuery (full version for AJAX support) -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<!-- Optionally, update to a more recent version if possible -->
<!-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

<!-- Total Calculation Code -->
<script>
function updateTotalOrder() {
  let total = 0;
  let errorOccurred = false;

  $('#order-detail table tbody tr').each(function() {
    const qty = validateNumeric($(this).find('input.change-quantity').val());
    const price = validateNumeric($(this).find('td:nth-child(3)').text());
    const itemTotal = qty * price;
    
    if (itemTotal < 0 || !Number.isInteger(itemTotal * 100)) {
      errorOccurred = true;
      return false;
    }
    total += itemTotal;
  });

  if (errorOccurred || !Number.isInteger(total * 100)) {
    console.error('Invalid calculation detected');
    return;
  }

  const formattedTotal = total.toLocaleString(undefined, {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });

  $('.totalAmount').text('Total Amount: Rs ' + formattedTotal);
  $('.btn-payment').attr('data-totalAmount', total);
}

let updateTimeout;
function debouncedUpdate() {
  clearTimeout(updateTimeout);
  updateTimeout = setTimeout(updateTotalOrder, 100);
}
</script>

<!-- Payment Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Payment</h5>
        <button tabindex="-4" type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h3 class="totalAmount"></h3>
        <h3 class="changeAmount">Service Charege: </h3>
        <div tabindex="-1" class="input-group mb-3">
          <div class="input-group-prepend">
            <span class="input-group-text">Rs</span>
          </div>
          <input tabindex="-2" type="number" id="recieved-amount" class="form-control">
        </div>
        <div class="form-group">
          <label tabindex="-1" for="payment">Payment Type</label>
          <select tabindex="-3" class="form-control" id="payment-type">
            <option tabindex="-2" value="cash">Cash</option>
            <option value="credit card">Credit Card</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button tabindex="-1" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-save-payment">Save Payment</button>
      </div>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  // Attach event listeners for recalculating total
  $('#order-detail').on('change', '.change-quantity', debouncedUpdate);
  $('#order-detail').on('click', '.btn-delete-saledetail', debouncedUpdate);

  $(document).ajaxComplete(function(event, xhr, settings) {
    if (settings.url.includes('/cashier/')) {
      debouncedUpdate();
    }
  });

  // Hide table details by default
  $("#table-detail").hide();

  // Load tables on window load
  window.onload = function() {
    if($("#table-detail").is(":hidden")){
      $.get("{{ url('/cashier/getTable') }}", function(data){
        $("#table-detail").html(data);
        $("#table-detail").slideDown('fast');
        $("#btn-show-tables").html('Hide Tables').removeClass('btn-primary').addClass('btn-dark');
      });
    } else {
      $("#table-detail").slideUp('fast');
      $("#btn-show-tables").html('View All Tables').removeClass('btn-danger').addClass('btn-primary');
    }
    setTimeout(function(){
      $("#table-detail .badge-success").first().click();
    }, 1000);
  };

  $(document).on('keyup', '#searchkeyword', function(e) {
    if ($(this).val().length > 2) {  
      e.preventDefault();
      var id = $(".nav-link.active").data("id");
      getmenuList(0);
    }
  });

  function getmenuList(id){
    var search_key = $("#searchkeyword").val().trim();
    $.get("{{ url('/cashier/getMenuByCategory') }}/" + id + "/" + search_key, function(data){
      $("#list-menu").hide();
      $("#list-menu").html(data);
      $("#list-menu").fadeIn('fast');
    });
  }

  // Load menus by category
  $(".nav-link").click(function(){
    var id = $(this).data("id");
    $("#searchkeyword").val('');
    getmenuList(id);
  });

  var SELECTED_TABLE_ID = "";
  var SELECTED_TABLE_NAME = "";
  var SALE_ID = "";

  // Show sale details when a table is selected
  $("#table-detail").on("click", ".btn-table", function(){
    SELECTED_TABLE_ID = $(this).data("id");
    SELECTED_TABLE_NAME = $(this).data("name");
    $("#selected-table").html('<br><h3>Table: ' + SELECTED_TABLE_NAME + '</h3><hr>');
    $.get("{{ url('/cashier/getSaleDetailsByTable') }}/" + SELECTED_TABLE_ID, function(data){
      $("#order-detail").html(data);
    });
  });

  $("#list-menu").on("click", ".btn-menu", function(){
    if (SELECTED_TABLE_ID == "") {
      alert("You need to select a table for the customer first");
    } else {
      var menu_id = $(this).data("id");
      $.ajaxSetup({
        headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
      });
      $.ajax({
        type: "POST",
        data: {
          "_token": $('meta[name="csrf-token"]').attr('content'),
          "menu_id": menu_id,
          "table_id": SELECTED_TABLE_ID,
          "table_name": SELECTED_TABLE_NAME,
          "quantity": 1
        },
        url: "{{ url('/cashier/orderFood') }}",
        success: function(data){
          $("#order-detail").html(data);
        }
      });
    }
  });

  $("#order-detail").on('click', ".btn-confirm-order", function(){
    var SaleID = $(this).data("id");
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "sale_id": SaleID
      },
      url: "{{ url('/cashier/confirmOrderStatus') }}",
      success: function(data){
        $("#order-detail").html(data);
      }
    });
  });

  // Delete sale detail
  $("#order-detail").on("click", ".btn-delete-saledetail", function(){
    var saleDetailID = $(this).data("id");
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "saleDetail_id": saleDetailID
      },
      url: "{{ url('/cashier/deleteSaleDetail') }}",
      success: function(data){
        $("#order-detail").html(data);
      }
    });
  });

  // Increase quantity
  $("#order-detail").on("click", ".btn-increase-quantity", function(){
    var saleDetailID = $(this).data("id");
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "saleDetail_id": saleDetailID
      },
      url: "{{ url('/cashier/increase-quantity') }}",
      success: function(data){
        $("#order-detail").html(data);
      }
    });
  });

  // Change quantity
  $("#order-detail").on("change", ".change-quantity", function(){
    var saleDetailID = $(this).data("id");
    var qty = Number($(this).val());
    if(qty < 1){
      $(this).val(1);
      qty = 1;
    }
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "saleDetail_id": saleDetailID,
        "qty": qty
      },
      url: "{{ url('/cashier/change-quantity') }}",
      success: function(data){
        $("#order-detail").html(data);
      }
    });
  });

  // Decrease quantity
  $("#order-detail").on("click", ".btn-decrease-quantity", function(){
    var saleDetailID = $(this).data("id");
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "saleDetail_id": saleDetailID
      },
      url: "{{ url('/cashier/decrease-quantity') }}",
      success: function(data){
        $("#order-detail").html(data);
      }
    });
  });

  // Print KOT
  $("#order-detail").on("click", ".printKot", function(){
    var saleID = $(this).data('id');
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "saleID": saleID
      },
      url: "{{ url('/cashier/printOrder') }}",
      success: function(data){
        window.open(data, '_blank').focus();
      }
    });
  });

  // Payment button click
  $("#order-detail").on("click", ".btn-payment", function(){
    var totalAmount = $(this).attr('data-totalAmount');
    $(".totalAmount").html("Total Amount Rs " + totalAmount);
    $("#recieved-amount").val(0);
    SALE_ID = $(this).data('id');
  });

  // Calculate change amount on keyup
  $("#recieved-amount").keyup(function(){
    var totalAmount = $(".btn-payment").attr('data-totalAmount');
    var recievedAmount = $(this).val();
    var changeAmount = recievedAmount ? recievedAmount : 0;
    $(".changeAmount").html("Service Charege: Rs " + changeAmount);
    // Enable or disable the save payment button based on input
    $('.btn-save-payment').prop('disabled', changeAmount < 0);
  });

  // Save payment
  $(".btn-save-payment").click(function(){
    var recievedAmount = $("#recieved-amount").val();
    var paymentType = $("#payment-type").val();
    var saleId = SALE_ID;
    $.ajax({
      type: "POST",
      data: {
        "_token": $('meta[name="csrf-token"]').attr('content'),
        "saleID": saleId,
        "recievedAmount": recievedAmount,
        "PaymentType": paymentType
      },
      url: "{{ url('/cashier/savePayment') }}",
      success: function(data){
        window.location.href = data;
      }
    });
  });

// Add these event handlers to your script section in index.blade.php
// Keep your existing Advance Payment button handler
$("#order-detail").on("click", ".btn-advance-payment", function(){
    var saleId = $(this).data('id');
    
    // Redirect directly to the function advance receipt page
    window.location.href = "{{ url('/cashier/showAdvanceRecipt') }}/" + saleId;
});

// Add a new handler for the Wedding Advance Payment button
$("#order-detail").on("click", ".btn-wedding-payment", function(){
    var saleId = $(this).data('id');
    
    // Redirect directly to the wedding advance receipt page
    window.location.href = "{{ url('/cashier/showAdvanceWeddingRecipt') }}/" + saleId;
});
});


</script>
@endsection
