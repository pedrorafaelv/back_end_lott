<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Level;
use DB;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Raffle\StoreRequest;
use App\Models\Ficha;
use App\Models\Card;
use App\Models\Raffle;
use App\Models\CheckCard;
use App\Models\CheckFull;
use App\Models\Group;
use App\Models\Account;
use App\Models\FichaGroupFicha;
use App\Models\GroupFicha;
use App\Models\FichaRaffle;
use App\Http\Controllers\Api\FichaController;
use Illuminate\Database\Query\JoinClause;

class RaffleController extends Controller
{
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $open_raffle= Raffle::where('group_id', $request->group_id)->whereNull('end_date')->get();

        if (count($open_raffle)==0){

            $raffle = new Raffle();
            if (!$request->name){
                $raffle->name = 'sorteo ';
            }else{
                $raffle->name = $request->name;
            }
            if (!$request->desription){
                $raffle->description =  $raffle->name;
            }else{
                $raffle->description = $request->description;
            }
            $raffle->user_id = $request->user_id;
            $raffle->group_id = $request->group_id;
            $raffle->groupficha_id = $request->groupficha_id;
            $raffle->total_amount= 0;
            $raffle->card_amount  = $request->card_amount;
            $raffle->minimun_play = $request->minimun_play;
            $raffle->maximun_play = $request->maximun_play;
            $raffle->maximun_user_play = $request->maximun_user_play;
            $raffle->retention_percent = $request->retention_percent;
            $raffle->retention_amount = $request->retention_amount;
            $raffle->admin_retention_percent = $request->admin_retention_percent;
            $raffle->admin_retention_amount = $request->admin_retention_amount;
            $raffle->raffle_type= $request->raffle_type;
            $raffle->privacy = $request->privacy;
            $raffle->reward_line = $request->reward_line;
            $raffle->percent_line = $request->percent_line;
            $raffle->reward_full = $request->reward_full;
            $raffle->percent_full = $request->percent_full;
            $raffle->admin_user = $request->admin_user;
            $raffle->scheduled_date = $request->scheduled_date;
            $raffle->scheduled_hour = $request->scheduled_hour;
            $raffle->time_zone = $request->time_zone; 
            $res = $raffle->save();
           // $res = "";
            if ($res){
                return response()->json($raffle, 200);
            }else{
                return response()->json(['message' => 'Error to create Raffle', 'group_id'=>$request->group_id], 401);
            }
        }
        return response()->json(['message' => 'Error to create Raffle, Raffle(s) in progress', 'open_raffle'=>$open_raffle,'group_id'=>$request->group_id], 401);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Raffle  $raffle
     * @return \Illuminate\Http\Response
     */
    public function show(Raffle $raffle)
    {
        return response()->json($raffle);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Raffle  $raffle
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Raffle $raffle)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        //
    }
    
    public function getFichas(Request $request){
        $i = 0;
        $fichas = Raffle::find($request->raffle_id)->Fichas;
        $Raffle  = Raffle::find($request->raffle_id);
        $f=array();
        $i = 0;
        if (count($fichas) > 0){
            foreach($fichas as $ficha){
                $ficha->image .= '@';
                $f[$i]= $ficha->name;
                $i++;
            }
        } 
        if (!empty($fichas)){
            return response()->json(['message'=>'success','Raffle'=> $Raffle, 'ArrFichas'=> $f, 'Fichas'=> $fichas], 200); 
        }
         return response()->json(['error' => 'Error to get Raffle data'],401 );
    }
    
 /** obtiene los datos de un sorteo */
    public function getRaffle(Request $request){

        $raffle =  Raffle::find($request->raffle_id);
        $groupficha = GroupFicha::find($raffle->groupficha_id); 
         if ($raffle !=""){
             return response()->json(['message'=>'Raffle encontrado','raffle'=>$raffle, 'groupficha'=>$groupficha], 200);
         }

        return response()->json(['error'=>'Raffle no encontrado'],400);
    }
/**
 * $request->user_id
 */
     /** obtiene los datos de un sorteo */
    public function getActiveRafflesByUser(Request $request){
        $raffles = DB::table('raffles')
            ->join('group_user', 'raffles.group_id', '=', 'group_user.group_id')
            ->join('users','users.id','=','group_user.user_id' )
            ->join('groupfichas','raffles.groupficha_id', '=', 'groupfichas.id')
            ->where('users.id', '=', $request->user_id )
            ->whereNull('raffles.end_date')
            ->select('raffles.id','raffles.group_id', 'raffles.name', 'raffles.card_amount', 'raffles.total_amount','groupfichas.name as groupfichas')
            ->get();
         if ($raffles !=""){
             return response()->json(['message'=>'Raffle encontrado','raffles'=>$raffles], 200);
         }
        return response()->json(['error'=>'Raffle no encontrado'],400);
    }



    /**
 * $request->user_id
 */
     /** obtiene los datos de un sorteo */
     public function getDetailActiveRafflesByUser(Request $request){
        $fichas = DB::table('ficha_groupfichas')
        ->select('groupficha_id', DB::raw('MIN(ficha_id) as first_ficha_id'))
        ->groupBy('groupficha_id');
        $descFichas= DB::table('fichas')
        ->select('fichas.id', 'fichas.name','fichas.image','fichas.description', 'min_fichas.groupficha_id')
        ->joinSub($fichas, 'min_fichas', function (JoinClause $join) {
                        $join->on('fichas.id', '=', 'min_fichas.first_ficha_id'); 
                     });
        $raffles = DB::table('raffles')
            ->join('group_user', 'raffles.group_id', '=', 'group_user.group_id')
            ->join('groups', 'groups.id', '=', 'group_user.group_id')
            ->join('users','users.id','=','group_user.user_id' )
            ->join('groupfichas','raffles.groupficha_id', '=', 'groupfichas.id')
            // ->join('ficha_groupfichas', 'ficha_groupfichas.groupficha_id', '=', 'raffles.groupficha_id')
            ->joinSub($descFichas, 'fichas', function (JoinClause $join) {
                $join->on('raffles.groupficha_id', '=', 'fichas.groupficha_id');
                
                 })
            ->where('users.id', '=', $request->user_id )
            ->whereNull('raffles.end_date')
            ->select('raffles.id','raffles.group_id', 'raffles.name', 'raffles.card_amount', 
            'raffles.total_amount','groupfichas.name as groupfichas', 'groups.name as nombregrupo',
            'fichas.name as ficha_name', 'fichas.image')
            ->get();
         if ($raffles !=""){
             return response()->json(['message'=>'Raffles encontrados','raffles'=>$raffles], 200);
         }
        return response()->json(['error'=>'Raffles no encontrados'],400);
    }
 /**
 * $request->group_id
 */
     /** obtiene los datos de un sorteo */
     public function getActiveRafflesByGroup(Request $request){
        $raffles = DB::table('raffles')
            ->where('raffles.group_id', '=', $request->group_id )
            ->join('groupfichas','raffles.groupficha_id', '=', 'groupfichas.id')
            ->whereNull('raffles.end_date')
            ->select('raffles.id','raffles.group_id','raffles.name', 'groupfichas.name as groupfichas')
            ->get();
         if ($raffles !=""){
             return response()->json(['message'=>'Raffle encontrado','raffles'=>$raffles], 200);
         }
        return response()->json(['message'=>'Raffle no encontrado'],400);
    }



/**
 * $request->raffle_id
 * $request->card_id
 * $request->user_id
 */
    public function putCard(Request $request){
        // validar que el sorteo este activo
        $raffle =  Raffle::find($request->raffle_id);
        if($raffle->end_date){
            return response()->json(
                ['success' => false,
                 'error'=>"009",
                 'code' => 'ERR-009',
                 'date'=> date("Y-m-d H:i:s"),
                 'message'=> 'Bets cannot be placed because the draw has already ended or results are currently being processed.'], 409);
        }
        // validar que no esten mas de 2 fichas en el sorteo
        $fichas_raffle = FichaRaffle::where([['raffle_id','=',$request->raffle_id]]);
        $count_fichas = count($fichas_raffle);
        if ($count_fichas > 2){
            // return response()->json(["message"=>"El raffle ya tiene asignadas las 2 primeras fichas"], 401);
            return response()->json(['success' => false,
                                      'error' =>"true",
                                      'code'   => 'ERR-009',
                                      'fichas' => $count_fichas,
                                      'date'   => date("Y-m-d H:i:s"),
                                      'message'=>'Bets cannot be placed because the draw has already ended or results are currently being processed.'], 409);
        }
        // validar la cuenta de dinero disponible del usuario
        $account = Account::find($request->user_id);
        if ($account->balance < $raffle->card_amount){    
            return response()->json(['success'=> false,
                                     'error'  =>'true',
                                     'code'   =>'ERR-010', 
                                     'dif'    => $account->balance - $raffle->card_amount,
                                     'date'   => date("Y-m-d H:i:s"),
                                     'message'=>"The user balance's its not enough"], 422);
        }
    // valida la disponibilidad del carton en el sorteo
        $i = 0;
        $cards = array();
        $cards_raffle = $raffle->Cards;
        if (count($cards_raffle)>0 ){
            foreach( $cards_raffle as $card){
                $cards[$i]= $card->id;  
                $i++;
            }
        }
        $i= count($cards) + 1;
        // echo '<pre> $card = ';print_r($cards); echo '</pre>';
        if (!in_array($request->card_id, $cards)){
            $res = $raffle->Cards()->attach($request->card_id, ['raffle_id' =>$request->raffle_id, 
                                                                'user_id'   =>$request->user_id, 
                                                                'indice'    => $i, 
                                                                'created_at'=>date("Y-m-d H:i:s"), 
                                                                'updated_at'=> date("Y-m-d H:i:s")]);
            return response()->json([ 'success'  =>true,
                                      'error'    =>false,
                                      'code'     =>'OK-000',
                                      'date'     => date("Y-m-d H:i:s"),
                                      'indice'   => $i, 
                                      'message'  =>'Card added successfully ',
                                      'card'     =>$request->card_id, 
                                      'raffle_id'=>$request->raffle_id], 200);
        }else {
            return response()->json([ 'success' =>false,
                                       'error'  =>true,
                                       'code'   => 'ERR-008',
                                       'message'=>'The user has already placed an identical bet for that number and draw. ',
                                       'card'   =>$request->card_id],409 );
        }
    }


    /**
 * Cancelar una apuesta (eliminar cartón de un sorteo)
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 * 
 * Request espera:
 * - raffle_id: ID del sorteo
 * - card_id: ID del cartón a cancelar
 * - user_id: ID del usuario (o se obtiene del token)
 */
public function cancelBet(Request $request)
{
    try {
        // 1. Validar datos de entrada
        $request->validate([
            'raffle_id' => 'required|exists:raffles,id',
            'card_id' => 'required|exists:cards,id',
            'user_id' => 'required|exists:users,id',
        ]);

        // 2. Obtener el sorteo
        $raffle = Raffle::find($request->raffle_id);
        
        // 3. Validar que el sorteo exista y esté activo
        if (!$raffle) {
            return response()->json([
                'success' => false,
                'error'   => true,
                'code'    => 'ERR-006',
                'message' => 'Raffle not found',
                'date'    => now()
            ], 404);
        }

        // 4. Validar que el sorteo NO haya terminado
        if ($raffle->end_date && now()->gt($raffle->end_date)) {
            return response()->json([
                'success' => false,
                'error'   => true,
                'code'    => 'ERR-009',
                'message' => 'Cannot cancel bet because the raffle has already ended',
                'date'    => now()
            ], 409);
        }

        // 5. Validar que el sorteo NO haya iniciado (si aplica)
        if ($raffle->start_date && now()->gt($raffle->start_date)) {
            return response()->json([
                'success' => false,
                'error'   => true,
                'code'    => 'ERR-016',
                'message' => 'Cannot cancel bet because the raffle has already started',
                'date'    => now()
            ], 409);
        }

        // 6. Verificar que la apuesta exista y pertenezca al usuario
        $bet = DB::table('card_raffle')
            ->where('raffle_id', $request->raffle_id)
            ->where('card_id', $request->card_id)
            ->where('user_id', $request->user_id)
            ->first();

        if (!$bet) {
            return response()->json([
                'success' => false,
                'error'   => true,
                'code'    => 'ERR-017',
                'message' => 'Bet not found or does not belong to this user',
                'date'    => now()
            ], 404);
        }

        // 7. Obtener la cuenta del usuario
        $account = Account::where('user_id', $request->user_id)->first();
        if (!$account) {
            return response()->json([
                'success' => false,
                'error'   => true,
                'code'    => 'ERR-018',
                'message' => 'User account not found',
                'date'    => now()
            ], 404);
        }

        // 8. Eliminar la apuesta (desvincular cartón del sorteo)
        $detached = $raffle->Cards()->detach($request->card_id);

        if ($detached > 0) {
            // 9. Reembolsar el dinero (si aplica)
            $cardAmount = $raffle->card_amount ?? 0;
            $account->balance += $cardAmount;
            $account->save();

            // 10. Registrar transacción (opcional)
            // Transaction::create([...]);

            return response()->json([
                'success' => true,
                'error'   => false,
                'code'    => 'OK-001',
                'message' => 'Bet cancelled successfully',
                'data'    => [
                            'raffle_id' => $request->raffle_id,
                            'card_id' => $request->card_id,
                            'user_id' => $request->user_id,
                            'refunded_amount' => $cardAmount,
                            'new_balance' => $account->balance,
                            ],
                'date'   => now()
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'error'   => true,
                'code'    => 'ERR-019',
                'message' => 'Failed to cancel the bet',
                'date' => now()
            ], 500);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error'   => true,
            'code'    => 'ERR-020',
            'message' => 'An error occurred while cancelling the bet: ' . $e->getMessage(),
            'date'    => now()
        ], 500);
    }
}

    /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * raffle_id
     * ficha_id
     */
    public function checkFullWinner(Request $request){
     
        $checkFull = new CheckFull();
        $cards = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
        ->join('check_fulls', 'cards.id', '=', 'check_fulls.card_id')
        ->where('card_ficha.ficha_id', '=', $request->ficha_id)
        ->where('card_raffle.raffle_id', '=', $request->raffle_id )
        ->where('check_fulls.raffle_id', '=', $request->raffle_id )
        ->select('cards.id','cards.combTotal' ,'check_fulls.nro_faltas as faltas' , 'check_fulls.faltantes as faltantes', 'check_fulls.id as checkFull_id', 'check_full.combinacion')
        ->orderBy('faltas')
        ->get();
        if ( count($cards) > 0 ){
            foreach($cards as $card){
                if ($card->faltas ===1){
                    $fullWinner[$i] = $card;
                    $i++;
                }elseif(count ($fullWinner) === 0) 
                {   
                    $fullWinner = array();
                    $array_faltantes = explode('|', $card->faltantes);
                    $checkFull->find($card->checkFull_id);
                    $checkFull->nro_faltas = $card->faltas -1;
                    if (($request->ficha_id= array_search($request->ficha_id, $array_faltantes)) !== false) {
                        unset($array_faltantes[$request->ficha_id]);
                    }
                    $checkFull-> implode('|',$array_faltantes);
                    $checkFull->save(); 
                } 
            }
            return response()->json(['winner'=>$fullWinner], 200);
        }
    }

   /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * raffle_id
     */

    public function newFicha(Request $request){ 
        
        //obtener las fichas de la tabla fichas_raffles
        $fichas_raffle = Raffle::find($request->raffle_id)->Fichas;
        $raffle= Raffle::find($request->raffle_id);
        $nroFicha = count($fichas_raffle);
        $i = 0;
         //se debe obtener dinamicamente el numero mayor de fichas 
        $groupfichas= DB::table('ficha_groupfichas')
        ->join('fichas', 'ficha_groupfichas.ficha_id', '=', 'fichas.id')
        ->where('ficha_groupfichas.groupficha_id', '=', $raffle->groupficha_id )
        ->where('fichas.active', 1)
        ->get();
        dd($groupfichas);
        if (!$groupfichas){
            return response()->json([
                    'success' => false,
                    'error'   => true,
                    'code'    => 'ERR-022',
                    'date'    => now(),
                    'message' => 'NO groupFichas found'], 404);
        }
        $maxFicha = $groupfichas->count();
        // $maxFicha = 375;

        if($nroFicha >0){
            foreach ($fichas_raffle as $ficha){
                $fichas[$i]= $ficha->id;
                $i++;
            }
        }else{
            $fichas = array();
        } 
        $indice= count($fichas) + 1;
        for( $j = 0; $j<=$maxFicha ; $j++){
            $f = Ficha::inRandomOrder()
            ->join('ficha_groupfichas', 'fichas.id', '=', 'ficha_groupfichas.ficha_id')
            ->where('active', 1)
            ->where ('ficha_groupfichas.groupficha_id', '=', $raffle->groupficha_id )
            ->first();
            //   dd($f);
            if(!$f){
                return response()->json([
                    'success' => false,
                    'error'   => true,
                    'code'    => 'ERR-02',
                    'date'    => now(),
                    'message' => 'No hay fichas disponibles para este sorteo'], 401);
            }
            if (!in_array($f->id, $fichas)){           
                $res =  $raffle->Fichas()->attach($f, ['raffle_id'=>$request->raffle_id,
                                                        'indice'=> $indice, 
                                                        'created_at'=>date("Y-m-d H:i:s"), 
                                                        'updated_at'=> date("Y-m-d H:i:s")]);
                return response()->json([
                    'success' => true,
                    'error'   => false,
                    'code'    => 'SUCCESS-01',
                    'date'    => now(),
                    'message' => 'Ficha asignada correctamente',
                    'ficha'   => $f
                ], 200);
            }
        }
     }
      
    
     /**
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     *
     */
    function getNewRecord(Request $request){
        //Obtener el sorteo
         $lineWinner = "0";
         $fullWinner = "0";
        $r = Raffle::find($request->raffle_id); 
        //verificar que el sorteo no esté cerrado
        if ( $r->end_date !== ""){    
            //obtener nueva ficha
            $ficha = $this->newFicha($request);
            //verificar que el sorteo tenga ganador de linea o lleno
            if ($r->reward_line === 1 && ($r->winner == ""|| $r->winner == null) ){
                // dd('$r', $r);
                //verificar ganador de línea
                $lineWinner = $this->checkLineWinner($request->raffle_id, $ficha->id );
                // guardar el line winner en el registro del raffle
                if($lineWinner != "" && ($r->winner =="" || $r->winner==null)){
                    $r->winner = $lineWinner;
                    $r->save();
                     if ($r->reward_full ==""|| $r->reward_full == null){
                        $this->endRaffle($request);
                        return response()->json ([ 'raffle' =>$r, 
                                                   'ficha'=> $ficha, 
                                                   'lineWinner'=> $lineWinner,
                                                   'message'=>'line winner found',
                                                   'success'=> true,
                                                   'code'=>'OK-000',
                                                   'fullWinner' => ""], 200);
                     }
                }
            }
            if ($r->reward_full == 1){
                //verificar si tiene ganador lleno
                $fullWinner = $this->checkFullW($r->id, $ficha->id);
                if($fullWinner != ""){
                    $r->full_winner =  $fullWinner;
                    $this->endRaffle($request); 
                    return response()->json ([  'success'=> true,
                                                'error'=> false,
                                                'code'=> 'OK-000',
                                                'message'=> 'full winner found',
                                                'raffle' =>$r, 
                                                'ficha'=> $ficha,
                                                'lineWinner'=>$lineWinner ,
                                                'fullWinner'=>$fullWinner], 200);
                }
               // guardar el line winner en el registro del raffle
            }
            return response()->json(['raffle'=>$r, 'ficha'=> $ficha], 200);  
        }else{
            return response()->json(['message' => 'End Raffle'], 401);
        }
    }


    /**
     * 
     * @param Request $request
     * @param raffle_id $raffle_id
     * @return \Illuminate\Http\Response
     * raffle_id
     * ficha_id
     */
    public function checkLineWinner($raffle_id, $ficha_id){
        // dd('checkLineWinner =>  raffle_id = '. $raffle_id.' ficha_id =  '. $ficha_id);
        $lines = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
        ->join('check_cards', 'cards.id', '=', 'check_cards.card_id')
        ->where('card_ficha.ficha_id', '=', $ficha_id)
        ->where('check_cards.raffle_id', '=', $raffle_id )
        ->where('card_raffle.raffle_id', '=', $raffle_id )
        ->select('cards.id as card_id', 'check_cards.nro_faltas as faltas' , 'check_cards.faltantes as faltantes', 'check_cards.id as checkCard_id')
        ->orderBy('faltas')
        ->get();
        $i=0;
        $z =array();
        // dd('entra en checkLineWinner');
        $lineWinner= array();
        $checkCards = new checkCard();
       
        if (count($lines) > 0){
            foreach($lines as $line){
                $checkCa= CheckCard::find($line->checkCard_id);
                $array_faltantes = explode('|', $checkCa->faltantes);
                if (($clave = array_search($ficha_id, $array_faltantes )) !== false) {
                    $checkCa->nro_faltas = $line->faltas -1;
                    unset($array_faltantes[$clave]);
                }
                if ($checkCa->nro_faltas == 0){
                    $line->nro_faltas = 0;
                    $fichas_linea= DB::table('card_ficha')
                    ->join('lineas_posicion', 'lineas_posicion.posicion', '=', 'card_ficha.posicion')
                    ->where('card_ficha.card_id', '=', $line->card_id )
                    ->where('lineas_posicion.linea', '=', $checkCa->linea )
                    ->select('card_ficha.ficha_id as ficha_id','lineas_posicion.linea as linea')
                    ->orderBy('linea')
                    ->get();
                    $z = 0;
                    $fich= array();
                    if (count($fichas_linea)>0){
                        foreach($fichas_linea as $fic){
                            $fich[$z] = $fic->ficha_id;
                            $z++;
                        }
                    }
                    $w= array();
                    $w['card_id'] =$line->card_id;
                    $w['fichas'] = $fich;
                    $w['linea']= $fichas_linea->linea;
                    // if (is_object($line)) {
                    //     // Gets the properties of the given object
                    //     // with get_object_vars function
                    //     $l = get_object_vars($line);
                    // }
                //   $line->append($fich);
                    //  if (is_array($l)){
                    //      array_push($fich, $line->card_id);
                    //  }
                    $lineWinner[$i] = $w;
                    $i++;
                }else{
                    $checkCa->faltantes= implode('|',$array_faltantes );
                }
                $checkCa->save();
            } 
            if (count($lineWinner)>0){
                //return response()->json(['resp'=>'Ganador encontrado','winner'=>$lineWinner], 200);
                return $lineWinner;
            }
        }
        //ingresar los card_id que no estaán en la tabla check_cards
        $cardswith4  = Card::select(DB::raw('z.*'))
                ->from(DB::raw('(select t.* from (select `cards`.id, `lineas_posicion`.`linea` from `cards` inner join `card_raffle` on `cards`.`id` = `card_raffle`.`card_id` inner join `card_ficha` on `cards`.`id` = `card_ficha`.`card_id` inner join `lineas_posicion` on `lineas_posicion`.`posicion` = `card_ficha`.`posicion` where `card_ficha`.`ficha_id` = '.$ficha_id.' and `card_raffle`.`raffle_id` = '.$raffle_id.' ) as t left join `check_cards` on `t`.`id` = `check_cards`.`card_id` and `check_cards`.`raffle_id` = '.$raffle_id.' and `check_cards`.`linea` = `t`.`linea` where `check_cards`.`id` is null) as z'))
                ->get();
        //echo '<pre> cardswith4 =';print_r($cardswith4); echo '</pre>';
        if ($cardswith4){
            foreach ($cardswith4 as $c){
                $card = Card::find($c->id);
                $checkC = new checkCard();
                $checkC->card_id = $card->id;
                $checkC->raffle_id = $raffle_id;
                $checkC->nro_faltas =4;
                $array_comb01= explode('|', $card->comb01);
                $array_comb02= explode('|', $card->comb02);
                $array_comb03= explode('|', $card->comb03);
                $array_comb04= explode('|', $card->comb04);
                $array_comb05= explode('|', $card->comb05);
                $array_comb06= explode('|', $card->comb06);
                $array_comb07= explode('|', $card->comb07);
                $array_comb08= explode('|', $card->comb08);
                $array_comb09= explode('|', $card->comb09);
                $array_comb10= explode('|', $card->comb10);
                $array_comb11= explode('|', $card->comb11);
                $array_comb12= explode('|', $card->comb12);
                $array_comb13= explode('|', $card->comb13);
                $array_comb14= explode('|', $card->comb14);
                $array_comb = array($array_comb01, $array_comb02, $array_comb03, $array_comb04, $array_comb05, $array_comb06, $array_comb07, 
                $array_comb08, $array_comb09, $array_comb10, $array_comb11, $array_comb12, $array_comb13, $array_comb14);
                if (strlen($c->linea) == 1){
                    $linea = '0'.$c->linea;
                } else{
                    $linea= $c->linea;
                } 
                if (in_array($ficha_id, $array_comb[$c->linea -1] ) ){
                    $checkC->linea =$c->linea;
                    $checkC->combinacion ='comb'.$linea;
                    if (($clave = array_search($ficha_id, $array_comb[$c->linea -1])) !== false) {
                        unset($array_comb[$c->linea-1][$clave]);
                    }
                    $checkC->faltantes= implode('|',$array_comb[$c->linea-1] );
                    $checkC->save();
                }
            }
            return false;
        }
    }
  
    /**
     * 
     * @param ficha_id $ficha_id
     * @param raffle_id $raffle_id
     * @return \Illuminate\Http\Response
     * raffle_id
     * ficha_id
     */
    public function checkFullW($raffle_id, $ficha_id){
    //  $raffle_id = $request->raffle_id;
    //  $ficha_id = $request->ficha_id;
        $cards = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
        ->join('check_fulls', 'cards.id', '=', 'check_fulls.card_id')
        ->where('card_ficha.ficha_id', '=', $ficha_id)
        ->where('card_raffle.raffle_id', '=', $raffle_id )
        ->where('check_fulls.raffle_id', '=', $raffle_id )
        ->select('cards.id','cards.combTotal' ,'check_fulls.nro_faltas as faltas' , 'check_fulls.faltantes as faltantes', 'check_fulls.id as checkFull_id')
        ->orderBy('faltas')
        ->get();
        if ( count($cards) > 0 ){
         //return $cards;
            $i = 0;
            $fullWinner = array();
            foreach($cards as $card){
                if ($card->faltas ==1){
                    $fullWinner[$i] = $card->id;
                    $i++;
                }elseif(count ($fullWinner) == 0){   
                    $array_faltantes = explode('|', $card->faltantes);
                    $checkF = CheckFull::find($card->checkFull_id); 
                    if (($ficha_id= array_search($ficha_id, $array_faltantes)) !== false) {
                        $checkF->nro_faltas = $card->faltas -1;
                        unset($array_faltantes[$ficha_id]);
                    }
                    $checkF->faltantes = implode('|', $array_faltantes);
                    $checkF->save(); 
                } 
            }
            return $checkF;
        }else{
            $cards = DB::table('cards')
            ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
            ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
           ->leftJoin('check_fulls', function ($join) {
            $join->on('cards.id', '=', DB::raw('check_fulls.card_id'))
                 ->where('check_fulls.raffle_id', '=', 'card_raffle.raffle_id')
                 ->whereNull('check_fulls.id');
        })
            ->where('card_ficha.ficha_id', '=', $ficha_id)
            ->where('card_raffle.raffle_id', '=', $raffle_id )
            ->whereNull('check_fulls.id' )
            ->select('cards.id', 'cards.combTotal as faltantes','cards.combTotal as combinacion')
            ->get();
            if ( count($cards) > 0 ){
                // return $cards;
                 foreach ($cards as $card){
                    $checkF = new checkFull();
                    $checkF->card_id = $card->id;
                    $checkF->raffle_id = $raffle_id;
                    $checkF->nro_faltas = 24;
                    $checkF->combinacion = $card->combinacion;
                    $array_faltantes = explode('|', $card->faltantes);
                    if (($ficha_id= array_search($ficha_id, $array_faltantes)) !== false) {
                        unset($array_faltantes[$ficha_id]);
                       }
                       $checkF->faltantes =  implode('|', $array_faltantes);
                        
                    //    $checkF->faltantes= $card->faltantes;
                       $checkF->save();
                       return $checkF;
                }
            }
        }
    }

    /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * raffle_id
     * start_date
     * start_hour
     */
    public function setStart(Request $request){
        $raffle = Raffle::find($request->raffle_id);
        $raffle->start_date= $request->start_date;
        $raffle->start_hour= $request->start_hour;
        $res= $raffle->save();
        if ($res){
            return response()->json(['message'=>'succefull','raffle',$raffle], 200);
        }else{
            return response()->json(['error' => 'Error to save Raffle', 'group_id'=> $request->raffle_id], 500);
        }
    }
    
    /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * raffle_id
     * end_date
     * end_hour
     */
    public function endRaffle(Request $request){
        $raffle = Raffle::find($request->raffle_id);
        $raffle->end_date= $request->end_date;
        $raffle->end_hour= $request->end_hour;
        $res= $raffle->save();
        if ($res){
            return response()->json(['message'=>$raffle], 200);
        }else{
            return response()->json(['error' => 'Error to save Raffle', 'group_id'=> $request->raffle_id], 500);
        }
    }

     /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * raffle_id
     * user_id
     */
     public function getCardsRaffleByUser(Request $request){
        $userCardsRaffle = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->join('raffles', 'raffles.id', '=', 'card_raffle.raffle_id')
        ->Join('users', 'users.id', '=', 'card_raffle.user_id')
        ->where('card_raffle.raffle_id', '=', $request->raffle_id )
        ->where('card_raffle.user_id', '=', $request->user_id )
        ->select('cards.*')
        ->get();
        $fichas = Raffle::find($request->raffle_id)->Fichas;
        foreach ($fichas as $ficha){
             $i = 0; $k =""; 
            //  echo '$ficha->id = '.$ficha->id; 
            foreach($userCardsRaffle as $card){
                $arr = explode('|', $card->combTotal);
                // echo '$card->id ='. $card->id; 
                $k = array_search($ficha->id, $arr);
                if ($k > -1 ){
                    //  echo '$k= '.$k. ' desc_pos'.$k; print_r($arr); 
                    $arr[$k] .= '@';                   
                    $i = $k+1;
                    if ($k < 9){
                        $nombre = 'desc_pos0'.$i;
                    } else{
                        $nombre = 'desc_pos'.$i;
                    }
                    $card->$nombre .= '@';
                    //echo '$card->$nombre = '.$card->$nombre. '<br>';
                }
                $card->combTotal = implode('|', $arr);
            }
        }
        return response()->json(['message'=>'success',
                                'Cards'=>$userCardsRaffle, 
                                'Fichas'=> $fichas,
                                'success' => true,
                                'error' => 'cards',
                                'code' => 'ERR-000',], 200);
     }

    /**
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     *
     */
    public function autoRaffle(Request $request){
        $raff = Raffle::find($request->raffle_id);
        $win = NULL;
        if ($raff->winner == NULL  && $raff->end_date == NULL){
            while ( $win== NULL) {
                // delay(2000);
               $winner= $this->getNewRecord( $request);
               $win= $winner->original['raffle']['end_date'];
               //echo 'ficha = <pre>';print_r($winner->original['ficha']); echo '</pre>';
            }
            return response()->json(['winner'=> $winner->original['raffle']]);
        }
    }
    
     /**
     * get the cards in a raffle.
     *
      * @param  \App\Models\card  $card
     * @return \Illuminate\Http\Response
     */ 
    public function getAvailableCardsByRaffle(Request $request)
{
    $request->validate([
        'raffle_id' => 'required|exists:raffles,id',
        'per_page' => 'nullable|integer|min:1|max:100'
    ]);

    $perPage = $request->input('per_page', 15);

    $AvailableCards = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->where('card_raffle.raffle_id', '=', $request->raffle_id)
        ->select(
            'cards.*',
            'card_raffle.id as card_raffle_id',
            'card_raffle.user_id',
            'card_raffle.indice',
            'card_raffle.status',
        )
        ->orderBy('card_raffle.indice', 'asc')
        ->paginate($perPage);

    if ($AvailableCards->isNotEmpty()) {
        return response()->json([
            'message' => 'Cards assigned to this raffle',
            'success' => true,
            'error' => false,
            'code' => 'OK-000',
            'raffle_id' => $request->raffle_id,
            'data' => $AvailableCards
        ], 200);
    }

    return response()->json([
        'message' => 'No cards assigned to this raffle',
        'success' => false,
        'error' => true,
        'code' => 'ERR-021',
        'raffle_id' => $request->raffle_id
    ], 404);
}
    
   
}
