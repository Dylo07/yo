@extends('layouts.app')

@section('content')


<div class="container">
  <div class="row">
  <div class="col">
    
  <table class="table table-dark">
 
  <tbody>
    <tr>
      <th scope="row">Date </th>
      <td><html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bootstrap 4 DatePicker</title>
    <script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <script src="https://unpkg.com/gijgo@1.9.14/js/gijgo.min.js" type="text/javascript"></script>
    <link href="https://unpkg.com/gijgo@1.9.14/css/gijgo.min.css" rel="stylesheet" type="text/css" />
</head>
<body>
    <input id="datepicker" width="276" />
    <script>
        $('#datepicker').datepicker({
            uiLibrary: 'bootstrap4'
        });
    </script>
</body>
</html></td>
      
    </tr>
    </tbody>
    <tr>
      <th scope="row">Type Of Transaction</th>
      <td><div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Select
  </button>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
  <a class="dropdown-item" href="#">Cash Withdraw</a>
    <a class="dropdown-item" href="#">Salary Advance</a>
    <a class="dropdown-item" href="#">Bill Payment</a>
    <a class="dropdown-item" href="#">Chicken Payment</a>
    <a class="dropdown-item" href="#">Fish Payment</a>
    <a class="dropdown-item" href="#">Grocery Payment</a>
    <a class="dropdown-item" href="#">Bakery Item</a>
  </div>

</div></td>
    </tr>
    <tr>
      <th scope="row">Employee</th>
      <td><div class="dropdown">
  <button class="btn btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
    Select
  </button>
  <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
    <a class="dropdown-item" href="#">None</a>
    <a class="dropdown-item" href="#">MD</a>
    <a class="dropdown-item" href="#">Mark</a>
    <a class="dropdown-item" href="#">Walker</a>
    
  </div>
</div></td>

    </tr>
    <tr>
      <th scope="row">Description</th>
      <td><div class="form-group">
      <textarea class="form-control" rows="5" id="comment"></textarea>
    </div>
  </form>
</div>
</td>

    </tr>
    <tr>
      <th scope="row">Amount</th>
      <td><div class="form-outline" style="width: 22rem;">
    <input  type="number" id="typeNumber" class="form-control" />
    
</div></td>
    
    
  </tbody>

</table>
<button type="button" class="btn btn-success btn-lg btn-block">ADD</button>

</div>






    <div class="col"><tr>
                
    <table class="table table-dark">
  <thead class="text-light bg-success">
    <tr>
      <th scope="col">Description</th>
      <th scope="col">Total </th>
      
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Bill Payment</th>
      <th scope="col">4000</th>
    </tr>
    <tr>
      <th scope="row">Salary Advance</th>
      <th scope="col">1000</th>
    </tr>
    <tr>
      <th scope="row">Withdrawal</th>
      <th scope="col">8000</th>
    </tr>
    </tbody>
    <tbody>
    <tr>
    
      <th scope="row">Total Amount</th>
      <th scope="col">13000</th>
    </tr>
  </tbody>
</table>
                
            </tr>
        </div>

    






<div class="p-5">
    
    

    
    <table class="table table-dark">
  <thead class="text-light bg-success">
    <tr>
      
      <th scope="col">Type of Transaction</th>
      <th scope="col">Employee</th>
      <th scope="col">Description</th>
      <th scope="col">Amount</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Cash Withdraw</th>
      <td>Mark</td>
      <td>withdraw to buy kitchen item</td>
      <td>10000</td>
      
    </tr>
    
  </tbody>
</table>
  </div>
</div>

</div>
@endsection
