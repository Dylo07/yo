<?php

namespace App\Http\Controllers;
use App\Models\PettycashTrans;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PettycashController extends Controller
{

    public function index()
    {
        $trans = PettycashTrans::orderBy('id','desc')->paginate(5);
        $summeries = DB::select('
                                SELECT REPLACE(pt.TypeOfTrans, "_", " ") AS TypeOfTrans,
                                (SELECT SUM(Amount) FROM pettycash_trans WHERE TypeOfTrans = pt.TypeOfTrans GROUP BY TypeOfTrans) AS total
                                FROM pettycash_trans pt
                                GROUP BY pt.TypeOfTrans
                            ');
        return view('pettycash.index', compact('trans', 'summeries'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'trans_date' => 'required',
            'TypeOfTrans' => 'required',
            'Employee' => 'required',
            'Description' => 'required',
            'Amount' => 'required',
        ]);
        
        PettycashTrans::create($request->post());

        return redirect()->route('pettycash')->with('success','Cash has been added successfully.');
    }


    public function edit($id)
    {
        return view('pettycash');
    }

    public function update(Request $request, PettycashTrans $trans)
    {
        $request->validate([
            'trans_date' => 'required',
            'TypeOfTrans' => 'required',
            'Employee' => 'required',
            'Description' => 'required',
            'Amount' => 'required',
        ]);
        
        $trans->fill($request->post())->save();

        return redirect()->route('companies.index')->with('success','Company Has Been updated successfully');
    }

    public function destroy($id)
    {
        DB::table('pettycash_trans')->delete($id);
        return redirect()->route('pettycash')->with('success','Company has been deleted successfully');
    }

}
