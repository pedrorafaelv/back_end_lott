<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Request\Api\FichaRequest;
use illuminate\Support\facades\Auth;
use illuminate\Http\Response;
use App\Models\Ficha;   

class FichaController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        return response()->json( Ficha::get());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    //    print_r($request);
        // $request->validated();
        $ficha= new Ficha();
        $ficha->name= $request->name;
        $ficha->active= $request->active;
        $res = $ficha->save();

         if ($res) {
             return response()->json(['message' => 'Ficha create succesfully', 'ficha' => $request->name ], 201);
         }
         return response()->json(['message' => 'Error to create ficha'], 500);

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Ficha $ficha)
    {
        return response()->json($ficha);
    }

   
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    } //
    
    public function getCards($ficha){
   
        $cards = Ficha::find($ficha)->Cards()->orderBy('id')->get();
        if ($cards) {
            return response()->json($cards, 201);
        }
        return response()->json(['message' => 'Error to get cards'], 500);
    }

}
