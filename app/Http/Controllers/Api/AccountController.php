<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Account;
use DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\RaffleController;
use App\Models\Raffle;


class AccountController extends Controller
{
    
    var  $vias = array(0 =>'transfer', 1 =>'award', 2 =>'bet', 3 =>'promotion', 4 =>'other');
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $account = new Account();
            $account->user_id= $request->user_id;
            $account->currency_code = $request->currency_code;
            $account->amount =$request->amount ;
            $account->credit = $request->credit;
            $account->deposit=$request->deposit;
            $account->withdrawal = $request->withdrawal;
            $account->via = $request->via;
            $account->description = $request->description;
            $account->comments = $request->comments;
            if($account->save()){
                return response()->json(['message'=>'aggregate prize amount','account'=>$account], 200);
            }
        return response()->json(['error'=>'dont not possible add'], 401);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function show(Account $account)
    {
        return response()->json($account);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function edit(Account $account)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Account $account)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Account  $account
     * @return \Illuminate\Http\Response
     */
    public function destroy(Account $account)
    {
        //
    }
/**
 * $request->currency
 * $request->user_id
 */
     public function getBalanceJson(Request $request){
        $balance=DB::table('accounts')
        ->where('user_id', '=', $request->user_id)
        ->where('currency_code', '=', $request->currency_code)
        ->get()->sum('amount');
        if($balance){
            return response()->json(['message'=>'balance','balance'=>$balance, 'currency_code'=>$request->currency_code], 200);
        }
         $balance = array();
        return response()->json(['error'=>'there are no movements in that currency', 'balance'=>$balance, 'currency_code'=>$request->currency_code], 200);
     }

/**
 * $request->currency
 * $request->user_id
 */
public function getBalance(Request $request){
    $balance=DB::table('accounts')
    ->where('user_id', '=', $request->user_id)
    ->where('currency_code', '=', $request->currency_code)
    ->get()->sum('amount');
    if($balance){
        return $balance;
    }
 }

/**
 * $request->currency
 * $request->user_id
 * $request->raffle_id
 */
    public function putAward(Request $request){

        $raffle = Raffle::find($request->raffle_id);
        $id= '"'. $raffle->id .'"';
        $acc = DB::table('accounts')
        ->where('accounts.comments', '=', $raffle->id)
        ->where('accounts.via', '=', 1)
        ->where('accounts.deposit', '=', 1)
        ->get();
        //return response()->json(['acc='=>$acc, 'count ='=>count($acc)]);
        if (count($acc)==0){
            $request->amount = $raffle->total_amount;
            $request->credit= 0;
            $request->credit_promotion= 0;
            $request->deposit= 1;
            $request->withdrawal=0;
            $request->via= 1;
            $request->description= 'Ganador sorteo = '.$request->raffle_id;
            $request->comments= $request->raffle_id;
            $account= $this->store($request);
        }
        if(isset($account)){
            return response()->json(['message'=>'Award add to account', 'account'=>$account], 200);
        }
        return response()->json(['error'=>'prize already collected'], 401); 

    }
/**
 * $request->currency
 * $request->user_id
 * $request->raffle_id
 */
    public function putBet(Request $request){

        $raffle = Raffle::find($request->raffle_id);
         $balance= $this->getBalance($request->user_id, $request->currency_code);
        if($raffle->card_amount > $balance){
            return response()->json(['error'=>'your Balance is not enough', 'balance'=>$balance], 401);
        }
        $request->amount = -1 * $raffle->card_amount;
        $request->credit= 0;
        $request->credit_promotion= 0;
        $request->deposit= 0;
        $request->withdrawal=1;
        $request->via= 2;
        $request->description= 'Apuesta '.$request->raffle_id;
        $request->comments= $request->raffle_id;
        $bet= $this->store($request);
        if(isset($balance)){
            return response()->json(['message'=>'Bet add', 'balance'=>$balance],200);
        }
        return response()->json(['error'=>'your Balance is not enough'], 401); 
 }
}
