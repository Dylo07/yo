@extends('layouts.app')

@section('styles')
<!-- FIXED: Enhanced CSS for modern table styling -->
<style>
.order-panel-right {
    position: fixed;
    right: 20px;
    top: 100px;
    width: 700px;
    max-height: 80vh;
    overflow-y: auto;
    background: white;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    z-index: 1000;
}

.main-content-left {
    margin-right: 490px; /* Space for right panel */
}

/* FIXED: Modern table grid styling */
.tables-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(70px, 1fr));
    gap: 6px;
    padding: 10px;
    background: white;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* FIXED: Modern table cards - simpler styling to match controller inline styles */
.table-card {
    background: #ffffff; /* Fallback */
    /* Border is handled by controller inline style */
    border-radius: 10px;
    padding: 5px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
    position: relative;
    min-height: 85px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
}

.table-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    /* Keep background white on hover to match "modern" look */
    background-color: #ffffff !important; 
}

/* Status classes primarily for selection logic now, styling is inline/controller based */
.table-card.available:hover {
    /* Border color handled by controller or specific overrides if needed */
}

.table-card.occupied:hover {
    /* Border color handled by controller */
}

/* Selected table styling - Needs to be distinct */
.table-card.selected {
    /* Force a distinct look for selected item */
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.3) !important;
    transform: translateY(-2px);
}

.table-card.selected::after {
    content: '✓';
    position: absolute;
    top: -5px;
    right: -5px;
    background: #10b981;
    color: white;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    font-weight: bold;
    border: 2px solid white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.table-icon {
    /* Icon styling handled by controller inline styles mostly */
    margin-bottom: 2px;
    font-size: 1.2rem;
}

.table-name {
    /* Name styling handled by controller */
    margin-bottom: 2px;
    font-size: 0.75rem;
    line-height: 1.1;
}

.table-status {
    /* Status badge styling handled by controller */
    font-size: 0.65rem;
    padding: 2px 6px;
}

/* Categories styling */
.nav-tabs {
    border: none;
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.nav-tabs .nav-link {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    margin: 3px;
    padding: 8px 16px;
    font-weight: 600;
    font-size: 0.85rem;
    color: #64748b;
    background: white;
    transition: all 0.2s ease;
}

.nav-tabs .nav-link:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    background: #f1f5f9;
    transform: translateY(-1px);
}

.nav-tabs .nav-link.active {
    background: #3b82f6;
    border-color: #3b82f6;
    color: white;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

/* Search box styling */
.search-sec {
    margin-bottom: 20px;
}

.search-sec .form-control {
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 15px;
    font-size: 0.9rem;
    transition: all 0.2s ease;
}

.search-sec .form-control:focus {
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

/* Menu items styling */
#list-menu {
    background: white;
    border-radius: 12px;
    padding: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.btn-menu {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 15px;
    margin: 8px;
    text-align: center;
    transition: all 0.2s ease;
    color: #1e293b;
    text-decoration: none;
    display: inline-block;
    min-height: 80px;
}

.btn-menu:hover {
    border-color: #3b82f6;
    color: #3b82f6;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.1);
    text-decoration: none;
}

/* Order panel header */
.order-panel-right h6 {
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    margin: -20px -20px 20px -20px;
    padding: 15px 20px;
    border-radius: 12px 12px 0 0;
    font-weight: 600;
    text-align: center;
}

@media (max-width: 1200px) {
    .order-panel-right {
        position: static;
        width: 100%;
        margin-top: 20px;
    }
    .main-content-left {
        margin-right: 0;
    }
    .tables-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
        gap: 8px;
        padding: 15px;
    }
}

@media (max-width: 768px) {
    .tables-grid {
        grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
        gap: 6px;
        padding: 10px;
    }
    
    .table-card {
        padding: 12px;
        min-height: 80px;
    }
    
    .table-icon {
        font-size: 1.5rem;
        margin-bottom: 4px;
    }
    
    .table-name {
        font-size: 0.8rem;
    }
    
    .table-status {
        font-size: 0.6rem;
        padding: 2px 6px;
    }
}
</style>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
@endsection

@section('content')
<div class="container">
  <div class="main-content-left">
    <div id="table-detail"></div>
    <div class="row justify-content-center py5">
      <div class="col-md-12"> <!-- CHANGED: Full width for left content -->
        <!-- MOVED: Categories and menu to main area -->
        <div class="mt-3">
          <nav>
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
              @foreach($categories as $category)
                <a class="nav-item nav-link btn-outline-success" data-id="{{ $category->id }}" data-bs-toggle="tab">
                  {{ $category->name }}
                </a>
              @endforeach
            </div>
          </nav>
          <br>
          <div class="row search-sec">
            <div class="col-md-6"></div>
            <div class="col-md-6 text-end">
              <input type="text" class="form-control" id="searchkeyword" placeholder="search menu" name="search">
            </div>
          </div>
          <div id="list-menu" class="row mt-2"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- MOVED: Order panel to fixed right position -->
<div class="order-panel-right">
  <h6><i class="fas fa-receipt me-2"></i>Current Order</h6>
  <div id="selected-table"></div>
  <div id="order-detail"></div>
</div>

<!-- Payment Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Payment</h5>
        <button tabindex="-4" type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <h3 class="totalAmount"></h3>
        <h3 class="changeAmount">Service Charge: </h3>
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
        <button tabindex="-1" type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary btn-save-payment">Save Payment</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<!-- Total Calculation Code -->
<script>

// FIXED: Validation function for numeric inputs
function validateNumeric(value) {
    const num = parseFloat(value);
    return isNaN(num) ? 0 : num;
}


function updateTotalOrder() {
    let total = 0;
    let hasErrors = false;

    $('#order-detail table tbody tr').each(function() {
        const qtyInput = $(this).find('input.change-quantity');
        const priceCell = $(this).find('td:nth-child(3)');
        
        if (qtyInput.length > 0 && priceCell.length > 0) {
            const qty = validateNumeric(qtyInput.val());
            const price = validateNumeric(priceCell.text());
            
            // Validate reasonable ranges
            if (qty < 0 || qty > 1000 || price < 0 || price > 100000) {
                console.error('Invalid values detected:', { qty, price });
                hasErrors = true;
                return false; // Break the loop
            }
            
            const itemTotal = qty * price;
            total += itemTotal;
            
            // Update the total cell for this row
            $(this).find('td:nth-child(4)').text(itemTotal.toFixed(2));
        }
    });

    if (hasErrors) {
        console.error('Calculation stopped due to invalid values');
        return;
    }

    // Format and display total
    const formattedTotal = total.toLocaleString('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });

    $('.totalAmount').text('Total Amount: Rs ' + formattedTotal);
    $('.btn-payment').attr('data-totalAmount', total.toFixed(2));
    
    // Log for debugging
    console.log('Total calculated:', total);
}

let updateTimeout;
function debouncedUpdate() {
    clearTimeout(updateTimeout);
    updateTimeout = setTimeout(function() {
        try {
            updateTotalOrder();
        } catch (error) {
            console.error('Error in total calculation:', error);
        }
    }, 150); // Slightly longer delay for stability
}
</script>

<script>
$(document).ready(function(){
    // Attach event listeners for recalculating total
    $('#order-detail').on('change', '.change-quantity', function() {
        const qty = validateNumeric($(this).val());
        if (qty < 1) {
            $(this).val(1);
        }
        debouncedUpdate();
    });
    
    $('#order-detail').on('click', '.btn-delete-saledetail', debouncedUpdate);

    // Recalculate after AJAX responses
    $(document).ajaxComplete(function(event, xhr, settings) {
        if (settings.url && settings.url.includes('/cashier/')) {
            setTimeout(debouncedUpdate, 100);
        }
    });

    // Load tables immediately without animation
    function loadTables() {
        // Add cache busting parameter to force fresh data
        $.get("/cashier/getTable?_=" + new Date().getTime(), function(data){
            $("#table-detail").html(data).show();
            
            // Auto-select first available table and category
            setTimeout(function(){
                $("#table-detail .table-card.available").first().click();
                $(".nav-link").first().click();
            }, 300);
        }).fail(function(xhr, status, error) {
            console.error('Error loading tables:', error);
            // Retry once after 1 second if failed
            setTimeout(loadTables, 1000);
        });
    };

  // SIMPLIFIED: Search function (back to original logic)
  $(document).on('keyup', '#searchkeyword', function(e) {
    if ($(this).val().length > 2) {  
      e.preventDefault();
      // Search across all categories when typing
      getmenuList(0);
    }
  });

  function getmenuList(id){
    var search_key = $("#searchkeyword").val().trim();
    // FIXED: Use relative URL to avoid HTTPS/HTTP conflicts
    var url = "/cashier/getMenuByCategory/" + id;
    if (search_key) {
        url += "/" + search_key;
    }
    
    $.get(url, function(data){
      $("#list-menu").hide();
      $("#list-menu").html(data);
      $("#list-menu").fadeIn('fast');
    }).fail(function(xhr, status, error) {
        console.error('Error loading menu:', error);
        $("#list-menu").html('<div class="alert alert-danger">Error loading menu items. Please try again.</div>');
    });
  }

  // SIMPLIFIED: Load menus by category (back to original working code)
  $(".nav-link").click(function(){
    $(".nav-link").removeClass('active');
    $(this).addClass('active');
    var id = $(this).data("id");
    $("#searchkeyword").val('');
    getmenuList(id);
  });

  var SELECTED_TABLE_ID = "";
  var SELECTED_TABLE_NAME = "";
  var SALE_ID = "";

  // FIXED: Show sale details when a table is selected (updated for modern cards)
  $(document).on("click", ".btn-table", function(){
    SELECTED_TABLE_ID = $(this).data("id");
    SELECTED_TABLE_NAME = $(this).data("name");
    
    // Update visual selection immediately for instant feedback
    $(".table-card").removeClass('selected');
    $(this).addClass('selected');
    
    // Show selected table immediately
    $("#selected-table").html('<div class="alert alert-info"><strong>Table: ' + SELECTED_TABLE_NAME + '</strong></div>');
    
    // Show loading indicator immediately for better UX
    $("#order-detail").html('<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading order details...</p></div>');
    
    // Add cache busting and fetch sale details
    $.get("/cashier/getSaleDetailsByTable/" + SELECTED_TABLE_ID + "?_=" + new Date().getTime(), function(data){
        $("#order-detail").html(data);
        
        // If no sale exists for this table, we need to create a new sale first
        if(data.includes("Not Found Any Sale Details for the Selected Table")) {
            // Create a new table interface for tables without existing orders
            var html = '<div class="alert alert-warning">Not Found Any Sale Details for the Selected Table</div>';
            html += '<hr><div class="text-center mt-4">';
            html += '<p>Click Advance Payment for Advance payments.</p>';
            html += '<a href="/cashier/setup-advance-payment/' + SELECTED_TABLE_ID + '" class="btn btn-primary btn-block mt-3">Advance Payment</a>';
            html += '</div>';
            $("#order-detail").html(html);
        }
    }).fail(function(xhr, status, error) {
        console.error('Error loading sale details:', error);
        $("#order-detail").html('<div class="alert alert-danger">Error loading order details. Please try again.</div>');
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
            url: "/cashier/orderFood",
            success: function(response){
                // Update the order details
                $("#order-detail").html(response.html);
                
                // FIXED: Update table status for modern cards
                if (response.tableStatusChanged) {
                    var $tableCard = $(`.table-card[data-id="${response.tableId}"]`);
                    $tableCard.removeClass('available').addClass('occupied');
                    $tableCard.find('.table-status').removeClass('bg-success').addClass('bg-warning').text('Occupied');
                    $tableCard.find('.table-icon').css('color', '#f59e0b');
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
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
      url: "/cashier/confirmOrderStatus",
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
      url: "/cashier/deleteSaleDetail",
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
      url: "/cashier/increase-quantity",
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
      url: "/cashier/change-quantity",
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
      url: "/cashier/decrease-quantity",
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
      url: "/cashier/printOrder",
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
    const totalAmount = parseFloat($(".btn-payment").attr('data-totalAmount')) || 0;
    const receivedAmount = validateNumeric($(this).val());
    
    // Service charge is typically the received amount
    const serviceCharge = receivedAmount;
    
    $(".changeAmount").html("Service Charge: Rs " + serviceCharge.toFixed(2));
    
    // Enable/disable save button based on reasonable input
    $('.btn-save-payment').prop('disabled', receivedAmount < 0);
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
      url: "/cashier/savePayment",
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
    window.location.href = "/cashier/showAdvanceRecipt/" + saleId;
});

// Add a new handler for the Wedding Advance Payment button
$("#order-detail").on("click", ".btn-wedding-payment", function(){
    var saleId = $(this).data('id');
    
    // Redirect directly to the wedding advance receipt page
    window.location.href = "/cashier/showAdvanceWeddingRecipt/" + saleId;
});

// Call loadTables immediately on document ready
loadTables();
});

// Admin-only: Delete confirmed item
$(document).on("click", ".btn-admin-delete-confirmed", function(){
    var saleDetailID = $(this).data("id");
    if (!confirm('Remove this confirmed item? This action will be logged.')) {
        return;
    }
    $.ajax({
        type: "POST",
        data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
            "saleDetail_id": saleDetailID
        },
        url: "/cashier/adminDeleteConfirmedItem",
        success: function(data){
            $("#order-detail").html(data);
            // Reload tables in case table was freed
            $.get("/cashier/getTable?_=" + new Date().getTime(), function(tableData){
                $("#table-detail").html(tableData);
            });
        },
        error: function(xhr){
            var msg = 'Failed to remove item.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            alert(msg);
        }
    });
});

// Admin-only: Clear Table function
function clearTable(saleId) {
    if (!confirm('Are you sure you want to clear this table?\n\nThis will remove ALL items and free the table. This action cannot be undone.')) {
        return;
    }
    $.ajax({
        type: "POST",
        url: "/cashier/clearTable",
        data: {
            "_token": $('meta[name="csrf-token"]').attr('content'),
            "sale_id": saleId
        },
        success: function(data) {
            if (data.success) {
                alert(data.message);
                // Reload tables to update status
                $.get("/cashier/getTable?_=" + new Date().getTime(), function(tableData){
                    $("#table-detail").html(tableData);
                });
                // Clear the order panel
                $("#order-detail").html('<div class="alert alert-success">Table cleared successfully.</div>');
                $("#selected-table").html('');
            } else {
                alert(data.error || 'Failed to clear table.');
            }
        },
        error: function(xhr) {
            var msg = 'Failed to clear table.';
            if (xhr.responseJSON && xhr.responseJSON.error) {
                msg = xhr.responseJSON.error;
            }
            alert(msg);
        }
    });
}


</script>
@endpush
@endsection