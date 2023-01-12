@extends('layouts.app')

@section('content')


<div class="container">
  <div class="row">
    <div class="col">
      <form action="{{ route('pettycash.store') }}" method="POST" enctype="multipart/form-data">
      @csrf
        <table class="table table-dark">
          <tbody>
            <tr>
              <th scope="row">Date</th>
              <td>
                <input type="date" name="trans_date" class="form-control" />
                @error('trans_date')
                  <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                @enderror
              </td>
            </tr>
            <tr>
              <th scope="row">Type Of Transaction</th>
              <td>
                  <select class="form-control" name="TypeOfTrans">
                    <option value="Cash_Withdraw">Cash Withdraw</option>
                    <option value="Salary_Advance">Salary Advance</option>
                    <option value="Bill_Payment">Bill Payment</option>
                    <option value="Chicken_Payment">Chicken Payment</option>
                    <option value="Fish_Payment">Fish Payment</option>
                    <option value="Grocery_Payment">Grocery Payment</option>
                    <option value="Bakery_Item">Bakery Item</option>
                  </select>
                  @error('TypeOfTrans')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
              </td>
            </tr>
            <tr>
              <th scope="row">Employee</th>
              <td>
                  <select class="form-control" name="Employee">
                    <option value="None">None</option>
                    <option value="MD">MD</option>
                    <option value="Mark">Mark</option>
                    <option value="Walker">Walker</option>
                  </select>
                  @error('Employee')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
              </td>
            </tr>
            <tr>
              <th scope="row">Description</th>
              <td>
                <div class="form-group">
                  <textarea class="form-control" rows="5" id="comment" name="Description"></textarea>
                </div>
                @error('Description')
                  <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                @enderror
              </td>
            </tr>
            <tr>
              <th scope="row">Amount</th>
              <td>
                <div class="form-outline">
                  <input  type="number" name="Amount" class="form-control" />
                  @error('Amount')
                    <div class="alert alert-danger mt-1 mb-1">{{ $message }}</div>
                  @enderror
                </div>
              </td>
          </tbody>
        </table>
        <button type="submit" class="btn btn-success btn-lg btn-block">ADD</button>
      </form>
</div>

  <div class="col">
    <tr>            
      <table class="table table-dark">
        <thead class="text-light bg-success">
          <tr>
            <th scope="col">Description</th>
            <th scope="col">Total </th>
          </tr>
        </thead>
        <tbody>
          <?php $gt = 0; ?>
          @foreach ($summeries as $sum)
            <tr>
              <th scope="row">{{ $sum->TypeOfTrans }}</th>
              <th scope="col">{{ $sum->total }}</th>
            </tr>
            <?php $gt = $gt + $sum->total; ?>
            @endforeach
        </tbody>
          <tr>
            <th scope="row">Total Amount</th>
            <th scope="col"><?php echo $gt; ?></th>
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
        <th scope="col">Date</th>
        <th scope="col">Action</th>
      </tr>
    </thead>
    <tbody>
    @foreach ($trans as $tran)
      <tr>
        <th scope="row">{{ $tran->TypeOfTrans() }}</th>
        <td>{{ $tran->Employee }}</td>
        <td>{{ $tran->Description }}</td>
        <td>{{ $tran->Amount }}</td>
        <td>{{ $tran->trans_date }}</td>
        <td>
              <a class="btn btn-primary" href="{{ route('pettycash.edit',$tran->id) }}">Edit</a>
              <a class="btn btn-danger" href="{{ route('pettycash.destroy',$tran->id) }}">Delete</a>
          </form>
        </td>
      </tr>
      @endforeach
    </tbody>
  </table>
  {!! $trans->links("pagination::bootstrap-4") !!}
</div>

</div>
</div>
@endsection
