<meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row" id="table-detail"></div>
    <div class="row justify-content-center py5">
      <div class="col-md-5">
        <button tabindex ="-2" class="btn btn-primary  btn-block" id="btn-show-tables">View All Tables</button>
        <div id="selected-table"></div>
        <div id="order-detail"></div>
      </div>
      <div class="col-md-7">
        <nav>
          <div class="nav nav-tabs  " id="nav-tab" role="tablist">
            @foreach($categories as $category)
            
              <a class="nav-item nav-link btn-outline-success"  data-id="{{$category->id}}" data-toggle="tab">
                {{$category->name}}
              </a>
              
            @endforeach
            
          </div>
        </nav>
        <br>
        <div class="row search-sec" >
          <div class="col-md-6 ">
          </div>
            
          <div class="col-md-6 pull-right">
              <input type="text" class="form-control" id="searchkeyword" placeholder="search menu" name="search">
          </div>

        </div>

        <div id="list-menu" class="row mt-2"></div>
      </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-Fy6S3B9q64WdZWQUiU+q4/2Lc9npb8tCaSX9FK7E8HnRr0Jz8D6OP9dO5Vg3Q9ct" crossorigin="anonymous"></script>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>

<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Payment</h5>
        <button tabindex ="-4" type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h3 class="totalAmount"></h3>
        <h3 class="changeAmount">Service Charege: </h3>
        <div tabindex ="-1" class="input-group mb-3">
           <div class="input-group-prepend">
            <span class="input-group-text">Rs</span>
           </div> 
           <input tabindex ="-2" type="number" id="recieved-amount" class="form-control" >
        </div>
        <div class="form-group">
          <label tabindex ="-1" for="payment">Payment Type</label>
          <select  tabindex ="-3" class="form-control" id="payment-type">
            <option tabindex ="-2" value="cash">Cash</option>
            <option value="credit card">Credit Card</option>
          </select>
        </div>
      
      </div>
      <div class="modal-footer">
        <button tabindex ="-1" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button  type="button" class="btn btn-primary btn-save-payment" >Save Payment</button>
      </div>
    </div>
  </div>
</div>

<script>
  
  $(document).ready(function(){
  // make table-detail hidden by default
  $("#table-detail").hide();


  //show all tables when load the page
    window.onload = function() {
      if($("#table-detail").is(":hidden")){
        $.get("{{url('/cashier/getTable')}}", function(data){
        $("#table-detail").html(data);
        $("#table-detail").slideDown('fast');
        $("#btn-show-tables").html('Hide Tables').removeClass('btn-primary').addClass('btn-dark');
      })
      }else{
        $("#table-detail").slideUp('fast');
        $("#btn-show-tables").html('View All Tables').removeClass('btn-danger').addClass('btn-primary');
      }

      setTimeout(function(){
        $( "#table-detail .badge-success" ).first().click();
        },1000);

  }
  
  $(document).on('keyup', '#searchkeyword', function(e) {
    console.log($(this).val().length);
    // if (e.keyCode === 13) {
      if ($(this).val().length > 2) {  
      e.preventDefault();
      //e.stopImmediatePropagation();
      //Do your stuff...
      var id = $(".nav-link.active").data("id");
      getmenuList(0);
    }
  });

  function getmenuList(id){
    var search_key  = $("#searchkeyword").val().trim();
    
    $.get("{{url('/cashier/getMenuByCategory')}}"+"/"+id+"/"+search_key,function(data){
      $("#list-menu").hide();
      $("#list-menu").html(data);
      $("#list-menu").fadeIn('fast');
    });
  }
    //load menus by category
    $(".nav-link").click(function(){
      var id = $(this).data("id");
      $("#searchkeyword").val('');
      getmenuList(id);
  })
  var SELECTED_TABLE_ID = "";
  var SELECTED_TABLE_NAME = "";
  var SALE_ID = "";
// detect button table onclick to show table data
$("#table-detail").on("click", ".btn-table", function(){
    SELECTED_TABLE_ID = $(this).data("id");
    SELECTED_TABLE_NAME = $(this).data("name");
    $("#selected-table").html('<br><h3>Table: '+SELECTED_TABLE_NAME+'</h3><hr>');
    $.get("{{url('/cashier/getSaleDetailsByTable')}}"+"/"+SELECTED_TABLE_ID, function(data){
      $("#order-detail").html(data);
    });
  });

  $("#list-menu").on("click", ".btn-menu", function(){
    if(SELECTED_TABLE_ID == ""){
      alert("You need to select a table for the customer first");
    }else{
      var menu_id = $(this).data("id");
      $.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});
      $.ajax({
        type: "POST",
        data: {
          "_token" : $('meta[name="csrf-token"]').attr('content'),
          "menu_id": menu_id,
          "table_id": SELECTED_TABLE_ID,
          "table_name": SELECTED_TABLE_NAME,
          "quantity" : 1
        },
        url: "{{url('/cashier/orderFood')}}",
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
        "_token" : $('meta[name="csrf-token"]').attr('content'),
        "sale_id" : SaleID
      },
      url: "{{url('/cashier/confirmOrderStatus')}}",
      success: function(data){
        $("#order-detail").html(data);
      }
    });
  });
// delete sale detail
$("#order-detail").on("click", ".btn-delete-saledetail", function(){
  var saleDetailID = $(this).data("id");
  $.ajax({
    type: "POST",
    data: {
      "_token" : $('meta[name="csrf-token"]').attr('content'),
      "saleDetail_id": saleDetailID
    },
    url: "{{url('/cashier/deleteSaleDetail')}}", 
    success: function(data){
      $(("#order-detail")).html(data);
      
    }

  })

});

//increase quantity
$("#order-detail").on("click", ".btn-increase-quantity", function(){
  var saleDetailID = $(this).data("id");
  $.ajax({
    type: "POST",
    data: {
      "_token" : $('meta[name="csrf-token"]').attr('content'),
      "saleDetail_id": saleDetailID
    },
    url: "{{url('/cashier/increase-quantity')}}", 
    success: function(data){
      $(("#order-detail")).html(data);
      
    }

  })

});
//increase quantity
$("#order-detail").on("change", ".change-quantity", function(){
  var saleDetailID = $(this).data("id");
  var qty = Number($(this).val());
  if(qty < 1){
    $(this).val(1);
    qty  = 1;
  }

  $.ajax({
    type: "POST",
    data: {
      "_token" : $('meta[name="csrf-token"]').attr('content'),
      "saleDetail_id": saleDetailID,
      "qty" : qty,
    },
    url: "{{url('/cashier/change-quantity')}}", 
    success: function(data){
      $(("#order-detail")).html(data);
      
    }

  })

});


//decrease quantity
$("#order-detail").on("click", ".btn-decrease-quantity", function(){
  var saleDetailID = $(this).data("id");
  $.ajax({
    type: "POST",
    data: {
      "_token" : $('meta[name="csrf-token"]').attr('content'),
      "saleDetail_id": saleDetailID
    },
    url: "{{url('/cashier/decrease-quantity')}}", 
    success: function(data){
      $(("#order-detail")).html(data);
      
    }

  })

});



// when user click on Print KOT
$("#order-detail").on("click",".printKot", function(){
  saleID = $(this).data('id');
  $.ajax({
  type: "POST",
  data: {
    "_token" : $('meta[name="csrf-token"]').attr('content'),
    "saleID" : saleID
  },
  url: "{{url('/cashier/printOrder')}}",
  success: function(data){
    window.open(data, '_blank').focus();
  }
});
});


// when a user click on payment button
$("#order-detail").on("click", ".btn-payment", function(){
  var totalAmount = $(this).attr('data-totalAmount');
  $(".totalAmount").html("Total Amount Rs " + totalAmount);
  $("#recieved-amount").val(0);
  SALE_ID = $(this).data('id');

});

//calculate change
$("#recieved-amount").keyup(function(){
var totalAmount = $(".btn-payment").attr('data-totalAmount');
var recievedAmount = $(this).val();
var changeAmount =  recievedAmount ;
if(!changeAmount){
  changeAmount = 0;
}
$(".changeAmount").html("Service Charege: Rs " + changeAmount );

//ckeck if cashier enter the right amount, then enable or disable save payment button

if(changeAmount >= 0){
  $('.btn-save-payment').prop('disabled', false);

}else{
  $('.btn-save-payment').prop('disabled', true);
}
});

// save payment
$(".btn-save-payment").click(function()
{
var recievedAmount = $("#recieved-amount").val();
var paymentType = $("#payment-type").val();
var saleId = SALE_ID;
$.ajax({
  type: "POST",
  data: {
    "_token" : $('meta[name="csrf-token"]').attr('content'),
    "saleID" : saleId,
    "recievedAmount" : recievedAmount,
    "PaymentType" : paymentType
  },
  url: "{{url('/cashier/savePayment')}}",
  success: function(data){
    window.location.href=data;
  }
});

});

});

</script>
@endsection


