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
use App\Models\CardRaffle; 
use App\Http\Controllers\Api\FichaController;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Str; 
use Illuminate\Support\Facades\Log; 


class RaffleController extends BaseApiController
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
                return response()->json(['succes'=> true,
                                        'error'=> false,
                                        'code'=>'001-OK',
                                        'message'=> 'raffle created', 
                                        'data'=>['raffle'=>$raffle]], 200);
            }else{
                return response()->json(['success'=>false,
                                            'error'=>true,
                                            'code'=>'ERR-039',
                                            'message'=>'raffle was not created',
                                            'data'=> ['group_id'=>$request->group_id]], 401);
            }
        }
        return response()->json(['success'=>false,
                                 'error'=> true,
                                 'code'=>'ERR-040',       
                                 'message' => 'Error to create Raffle, Raffle(s) in progress', 'open_raffle'=>$open_raffle,'group_id'=>$request->group_id], 401);
    }
    
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Raffle  $raffle
     * @return \Illuminate\Http\Response
     */
    public function show(Raffle $raffle)
    {
        $raffle = Raffle::find($raffle->id);
        if (!$raffle) {
            return response()->json(['error' => true,
                                     'code' => 'ERR-006',
                                     'success' => false,
                                     'message' => 'Raffle not found'], 404);
        }
        return response()->json(['error'=>false,
                                 'code'=>'OK-000',
                                 'success'=>true,
                                 'message'=>'Raffle found',
                                 'data'=>['raffle'=>$raffle]]);
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
    public function getRaffleDetails(Request $request){

        $raffle =  Raffle::find($request->raffle_id);
        $groupficha = GroupFicha::find($raffle->groupficha_id); 
         if ($raffle !=""){
             return response()->json(['success'=>true,
                                      'error'=>false,
                                      'code'=>'OK-000',
                                      'message'=>'Raffle encontrado',
                                      'data'=>
                                      [
                                      'raffle'=>$raffle,
                                      'groupficha'=>$groupficha
                                      ],
                                      'date'=> date("Y-m-d H:i:s")
                                      ], 200);
         }

        return response()->json([
            'success'=>false,
            'code'=>'ERR-001',
            'error'=>'Raffle not found',
            'message'=>'Nose encontro el sorteo'],404);
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
 * Hacer una apuesta (agregar cartón a un sorteo)
 * 
 * @param Request $request
 * @return \Illuminate\Http\JsonResponse
 * 
 * $request->raffle_id
 * $request->card_id
 * $request->user_id
 */
public function putCard(Request $request)
{
    try {
        // 1. Validar que el sorteo existe
        $raffle = Raffle::find($request->raffle_id);
        if (!$raffle) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-006',
                'date' => now()->toDateTimeString(),
                'message' => 'Raffle not found'
            ], 404);
        }

        // 2. Validar que el sorteo esté activo (no cerrado)
        if ($raffle->end_date && $raffle->end_date != '0000-00-00 00:00:00' && now()->gt($raffle->end_date)) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-009',
                'date' => now()->toDateTimeString(),
                'data' => ['raffle_id' => $raffle->id, 'end_date' => $raffle->end_date],
                'message' => 'Bets cannot be placed because the draw has already ended'
            ], 409);
        }

        // 3. Validar que el sorteo haya iniciado
        if (!$raffle->start_date || now()->lt($raffle->start_date)) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-023',
                'date' => now()->toDateTimeString(),
                'data' => ['raffle_id' => $raffle->id, 'start_date' => $raffle->start_date],
                'message' => 'Raffle has not started yet'
            ], 409);
        }

        // 4. Validar límite de fichas (dinámico)
        $fichasCount = FichaRaffle::where('raffle_id', $request->raffle_id)->count();
        $maxFichas = $raffle->maximun_play ?? 2; // Usar valor de la tabla o default 2
        
        if ($fichasCount >= $maxFichas) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-00',
                'date' => now()->toDateTimeString(),
                'data' => [
                    'fichas_actuales' => $fichasCount,
                    'fichas_maximas' => $maxFichas
                ],
                'message' => "Cannot place bet. Maximum number of figures ({$maxFichas}) already assigned."
            ], 409);
        }

        // 5. Validar cuenta del usuario
        $account = Account::where('user_id', $request->user_id)->latest('created_at')->first();
        if (!$account) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-029',
                'date' => now()->toDateTimeString(),
                'message' => "User doesn't have an account"
            ], 404);
        }

        // 6. Validar saldo suficiente
        $cardAmount = $raffle->card_amount ?? 0;
        if ($account->amount < $cardAmount) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-010',
                'date' => now()->toDateTimeString(),
                'data' => [
                    'account_balance' => $account->amount,
                    'card_amount' => $cardAmount,
                    'difference' => $cardAmount - $account->amount
                ],
                'message' => "Insufficient balance"
            ], 422);
        }

        // 7. Verificar disponibilidad del cartón
        $cardRaffle = CardRaffle::where('raffle_id', $request->raffle_id)
            ->where('card_id', $request->card_id)
            ->first();

        // ✅ Si el cartón ya está asignado a otro usuario
        if ($cardRaffle && $cardRaffle->user_id !== null) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-011',
                'date' => now()->toDateTimeString(),
                'message' => "Card is already assigned to another user",
                'data' => [
                    'card_id' => $request->card_id,
                    'assigned_to' => $cardRaffle->user_id
                ]
            ], 409);
        }

        // ✅ Iniciar transacción para garantizar integridad
        DB::beginTransaction();

        try {
            // 8. Asignar el cartón al usuario
            if (!$cardRaffle) {
                // Crear nuevo registro si no existe
                $cardRaffle = CardRaffle::create([
                    'raffle_id' => $request->raffle_id,
                    'card_id' => $request->card_id,
                    'user_id' => $request->user_id,
                    'indice' => CardRaffle::where('raffle_id', $request->raffle_id)->count() + 1,
                    'status' => 'active',
                    'active' => 1,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                // Actualizar registro existente
                $cardRaffle->user_id = $request->user_id;
                $cardRaffle->status = 'active';
                $cardRaffle->updated_at = now();
                $cardRaffle->save();
            }

            // 9. Descontar el saldo y registrar transacción
            $newBalance = $account->amount - $cardAmount;
            
            $transaction = Account::create([
                'user_id' => $request->user_id,
                'currency_code' => $account->currency_code ?? 'EUR',
                'amount' => $newBalance,
                'withdrawal' => $cardAmount,  // Si el campo existe
                'via' => 3, // Bet
                'description' => "Bet for card {$request->card_id} in raffle {$request->raffle_id}",
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // 10. Actualizar el saldo de la cuenta
            $account->amount = $newBalance;
            $account->save();

            // ✅ Confirmar transacción
            DB::commit();

            return response()->json([
                'success' => true,
                'error' => false,
                'code' => 'OK-000',
                'date' => now()->toDateTimeString(),
                'data' => [
                    'card_id' => $request->card_id,
                    'raffle_id' => $request->raffle_id,
                    'user_id' => $request->user_id,
                    'indice' => $cardRaffle->indice,
                    'account_balance' => $newBalance,
                    'transaction_id' => $transaction->id
                ],
                'message' => 'Card added successfully'
            ], 200);

        } catch (\Exception $e) {
            // ❌ Si algo falla, revertir transacción
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-030',
                'date' => now()->toDateTimeString(),
                'message' => 'Transaction failed: ' . $e->getMessage()
            ], 500);
        }

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => true,
            'code' => 'ERR-500',
            'date' => now()->toDateTimeString(),
            'message' => 'Unexpected error: ' . $e->getMessage()
        ], 500);
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
        // ✅ Generar request_id único para trazabilidad
        $requestId = (string) Str::uuid();
        $timestamp = now()->toDateTimeString();

        try {
            // 1. Validar datos de entrada
            $validator = validator([
                'raffle_id' => $request->raffle_id,
                'card_id'   => $request->card_id,
                'user_id'   => $request->user_id,
            ], [
                'raffle_id' => 'required|exists:raffles,id',
                'card_id'   => 'required|exists:cards,id',
                'user_id'   => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                return $this->errorResponse(
                    'ERR-001',
                    'Validation error',
                    [
                        'errors'     => $validator->errors()->toArray(),
                        'request_id' => $requestId,
                    ],
                    422,
                    $requestId
                );
            }

            // 2. Obtener el sorteo
            $raffle = Raffle::find($request->raffle_id);

            if (!$raffle) {
                return $this->errorResponse(
                    'ERR-006',
                    'Raffle not found',
                    [
                        'request_id' => $requestId,
                        'success'    => null,
                    ],
                    404,
                    $requestId
                );
            }

            // 3. Validar que el sorteo no haya terminado
            if ($raffle->end_date 
                && $raffle->end_date != '0000-00-00 00:00:00' 
                && now()->gt($raffle->end_date)
            ) {
                return $this->errorResponse(
                    'ERR-009',
                    'Cannot cancel bet because the raffle has already ended',
                    ['end_date' => $raffle->end_date],
                    409,
                    $requestId
                );
            }

            // 4. Validar límite de fichas
            $countFichas = DB::table('ficha_raffle')
                ->where('raffle_id', $request->raffle_id)
                ->count();

            $maxFichas = $raffle->maximun_play ?? 2;

            if ($raffle->start_date 
                && now()->gt($raffle->start_date) 
                && $countFichas > $maxFichas
            ) {
                return $this->errorResponse(
                    'ERR-016',
                    "Cannot cancel bet. The raffle has already started and the maximum number of figures ({$maxFichas}) has been reached.",
                    [
                        'raffle_id'        => $raffle->id,
                        'start_date'       => $raffle->start_date,
                        'fichas_asignadas' => $countFichas,
                        'fichas_maximas'   => $maxFichas,
                    ],
                    409,
                    $requestId
                );
            }

            // 5. Verificar que la apuesta exista y pertenezca al usuario
            $bet = DB::table('card_raffle')
                ->where('raffle_id', $request->raffle_id)
                ->where('card_id', $request->card_id)
                ->where('user_id', $request->user_id)
                ->first();

            if (!$bet) {
                return $this->errorResponse(
                    'ERR-017',
                    'Bet not found or does not belong to this user',
                    null,
                    404,
                    $requestId
                );
            }

            // 6. Obtener la cuenta del usuario
            $account = Account::where('user_id', $request->user_id)
                ->latest()
                ->first();

            if (!$account) {
                return $this->errorResponse(
                    'ERR-018',
                    'User account not found',
                    null,
                    404,
                    $requestId
                );
            }

            // 7. Iniciar transacción
            DB::beginTransaction();

            try {
                // 8. Eliminar la apuesta
                $detached = $raffle->Cards()->detach($request->card_id);

                if ($detached <= 0) {
                    DB::rollBack();
                    return $this->errorResponse(
                        'ERR-019',
                        'Failed to cancel the bet',
                        null,
                        500,
                        $requestId
                    );
                }

                // 9. Reembolsar el dinero
                $cardAmount = $raffle->card_amount ?? 0;
                $account->amount += $cardAmount;
                $account->save();

                // 10. Registrar transacción de reembolso (opcional)
                // Transaction::create([...]);

                DB::commit();

                // ✅ RESPUESTA DE ÉXITO
                return $this->successResponse(
                    'OK-001',
                    'Bet cancelled successfully',
                    [
                        'raffle_id'       => (int) $request->raffle_id,
                        'card_id'         => (int) $request->card_id,
                        'user_id'         => (int) $request->user_id,
                        'refunded_amount' => (float) $cardAmount,
                        'new_amount'      => (float) $account->amount,
                    ],
                    200,
                    $requestId
                );

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error in cancelBet: ' . $e->getMessage(), [
                'request_id' => $requestId,
                'trace'      => $e->getTraceAsString(),
                'request'    => $request->all()
            ]);

            return $this->errorResponse(
                'ERR-020',
                'An error occurred while cancelling the bet',
                config('app.debug') ? ['exception' => $e->getMessage()] : null,
                500,
                $requestId
            );
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
     * ✅ ENDPOINT PÚBLICO - Obtener nueva ficha para el sorteo
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNewRecord(Request $request)
    {
        try {
            // 1. Validar que existe el sorteo
            $raffle = Raffle::find($request->raffle_id);
            
            if (!$raffle) {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'code' => 'ERR-006',
                    'message' => 'Raffle not found'
                ], 404);
            }

            // 2. Validar que el sorteo NO esté cerrado
            if (($raffle->end_date && now()->gt($raffle->end_date))&& $raffle->end_date != null && $raffle->end_date != "" &&$raffle->end_date !="0000-00-00 00:00:00") {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'code' => 'ERR-009',
                    'data' => [
                        'raffle_id' => $raffle->id,
                        'end_date' => $raffle->end_date
                    ],
                    'message' => 'Raffle has already ended'
                ], 409);
            }

            // 3. Validar que el sorteo YA HAYA INICIADO
            if (!$raffle->start_date || now()->lt($raffle->start_date)) {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'code' => 'ERR-023',
                    'message' => 'Raffle has not started yet',
                    'start_date' => $raffle->start_date
                ], 409);
            }

            // 4. Validar que haya al menos un tipo de premio habilitado
            if (($raffle->reward_line == 0 || $raffle->reward_line == null)  &&  ($raffle->reward_full==0 || $raffle->reward_full == null) ) {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'code' => 'ERR-026',
                    'message' => 'Raffle has no prizes enabled (reward_line and reward_full are disabled)',
                    'reward_line' => $raffle->reward_line,
                    'reward_full' => $raffle->reward_full
                ], 409);
            }

            // 5. ✅ Si solo hay premio de línea 
            //    El sorteo termina cuando hay un ganador de línea
            if ($raffle->reward_line != 0 && ($raffle->reward_full == 0 || $raffle->reward_full == null)) {
                if ($raffle->winner) {
                    return response()->json([
                        'success' => false,
                        'error' => true,
                        'code' => 'ERR-024',
                        'message' => 'Raffle already has a line winner and has ended',
                        'winner' => $raffle->winner
                    ], 409);
                }
            }

            // 6. ✅ Si hay premio de full (reward_full != 0) - CON o SIN línea
            //    El sorteo SOLO termina con cartón lleno, aunque haya ganador de línea
            if ($raffle->reward_full == 1) {
                if ($raffle->full_winner) {
                    return response()->json([
                        'success' => false,
                        'error' => true,
                        'code' => 'ERR-025',
                        'message' => 'Raffle already has a full winner and has ended',
                        'full_winner' => $raffle->full_winner
                    ], 409);
                }
            }

            // 7. Obtener una nueva ficha
            $result = $this->assignNextFicha($request->raffle_id);
            
            if (!$result['success']) {
                return response()->json([
                    'success' => false,
                    'error' => true,
                    'code' => 'ERR-002',
                    'message' => $result['message']
                ], 404);
            }

            $ficha = $result['ficha'];
            $lineWinner = null;
            $fullWinner = null;
            $raffleClosed = false;
            $winnersFound = [];

            // 8. ✅ Verificar ganador de línea SOLO si está habilitado
            if (($raffle->reward_line!= 0 || $raffle_reward_line != null) && !$raffle->winner) {
                $lineWinner = $this->checkLineWinner($request->raffle_id, $ficha->id);
                if ($lineWinner) {
                    $raffle->winner = $lineWinner;
                    $raffle->save();
                    $winnersFound[] = 'line';
                    
                    // ✅ Si SOLO hay premio de línea (reward_full = 0 o NULL), el sorteo termina
                    if ($raffle->reward_full !=0 && $raffle->reward_full != null) {
                        $raffleClosed = true;
                    } else {
                        // ✅ Si también hay premio de full, el sorteo CONTINÚA
                        $raffleClosed = false;
                    }
                }
            }

            // 9. ✅ Verificar ganador de cartón lleno SOLO si está habilitado
            if ($raffle->reward_full != 0 && $raffle->reward_full != null && !$raffle->full_winner) {
                $fullWinner = $this->checkFullW($raffle->id, $ficha->id);
                if ($fullWinner) {
                    $raffle->full_winner = $fullWinner;
                    $raffle->save();
                    $winnersFound[] = 'full';
                    
                    // ✅ SIEMPRE que hay full winner, el sorteo termina
                    $raffleClosed = true;
                }
            }

            // 10. Si hay ganador y el sorteo debe cerrarse, cerrarlo
            if ($raffleClosed) {
                $this->closeRaffle($request->raffle_id);
            }

            // 11. Recargar el modelo
            $raffle->refresh();

            // 12. Determinar el mensaje según el tipo de ganador
            $message = 'Ficha asignada correctamente';
            if ($raffleClosed) {
                if (in_array('full', $winnersFound)) {
                    $message = '🎉 Sorteo finalizado - Ganador de CARTÓN LLENO';
                } elseif (in_array('line', $winnersFound) && $raffle->reward_full != 1) {
                    $message = '🎉 Sorteo finalizado - Ganador de LÍNEA';
                }
            } else {
                if (in_array('line', $winnersFound) && $raffle->reward_full == 1) {
                    $message = '✅ Ganador de LÍNEA registrado. El sorteo CONTINÚA hasta tener CARTÓN LLENO';
                }
            }

            return response()->json([
                'success' => true,
                'error' => false,
                'code' => 'OK-000',
                'message' => $message,
                'data' => [
                    'raffle' => [
                        'id' => $raffle->id,
                        'name' => $raffle->name,
                        'status' => $raffle->end_date ? 'closed' : 'active',
                        'reward_line' => $raffle->reward_line,
                        'reward_full' => $raffle->reward_full,
                        'line_winner' => $raffle->winner,
                        'full_winner' => $raffle->full_winner,
                        'total_fichas' => $raffle->Fichas()->count(),
                        'is_closed' => $raffleClosed
                    ],
                    'ficha' => $ficha,
                    'winners' => [
                        'line' => $lineWinner,
                        'full' => $fullWinner
                    ]
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-500',
                'message' => 'Error processing request: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 🔒 MÉTODO PRIVADO - Asignar una nueva ficha al sorteo
     */
    private function assignNextFicha($raffleId)
    {
        try {
            DB::beginTransaction();

            $raffle = Raffle::find($raffleId);
            
            if (!$raffle) {
                return ['success' => false, 'message' => 'Raffle not found'];
            }

            $fichasAsignadas = $raffle->Fichas()->pluck('fichas.id')->toArray();
            $indice = count($fichasAsignadas) + 1;

            $groupFichas = DB::table('ficha_groupfichas')
                ->join('fichas', 'ficha_groupfichas.ficha_id', '=', 'fichas.id')
                ->where('ficha_groupfichas.groupficha_id', '=', $raffle->groupficha_id)
                ->where('fichas.active', 1)
                ->pluck('fichas.id')
                ->toArray();
//  dd($groupFichas);
            if (empty($groupFichas)) {
                return ['success' => false, 'message' => 'No fichas available in this group', $groupFichas];
            }

            $fichasDisponibles = array_diff($groupFichas, $fichasAsignadas);

            if (empty($fichasDisponibles)) {
                return ['success' => false, 'message' => 'No available fichas for this raffle'];
            }

            $fichaId = $fichasDisponibles[array_rand($fichasDisponibles)];
            $ficha = Ficha::find($fichaId);

            if (!$ficha) {
                return ['success' => false, 'message' => 'Selected ficha not found'];
            }

            $raffle->Fichas()->attach($fichaId, [
                'raffle_id' => $raffleId,
                'indice' => $indice,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            DB::commit();

            return ['success' => true, 'ficha' => $ficha];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => 'Error assigning ficha: ' . $e->getMessage()];
        }
    }

    /**
     * 🔒 MÉTODO PRIVADO - Cerrar el sorteo
     */
    private function closeRaffle($raffleId)
    {
        try {
            $raffle = Raffle::find($raffleId);
            if ($raffle) {
                $raffle->end_date = now();
                $raffle->save();
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    /**
     * ENDPOINT PÚBLICO - Finalizar el sorteo manualmente
     */
    public function endRaffle(Request $request)
    {
        $raffle = Raffle::find($request->raffle_id);
        
        if (!$raffle) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-006',
                'message' => 'Raffle not found'
            ], 404);
        }

        if ($raffle->end_date) {
            return response()->json([
                'success' => false,
                'error' => true,
                'code' => 'ERR-009',
                'message' => 'Raffle is already closed'
            ], 409);
        }

        $raffle->end_date = now();
        $raffle->save();

        return response()->json([
            'success' => true,
            'error' => false,
            'code' => 'OK-000',
            'message' => 'Raffle ended successfully',
            'raffle' => $raffle
        ], 200);
    }

    // Fin de código optimizado con deepseek )

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
     * @param Request $request
     * @return \Illuminate\Http\Response
     */ 
    public function getAvailableCardsByRaffle(Request $request, $raffle_id)
{
    $request->validate([
        'per_page' => 'nullable|integer|min:1|max:100',
    ]);

    $perPage = $request->input('per_page', 50);
    $userId = $request->input('user_id');   

    $query = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->where('card_raffle.raffle_id', '=', $raffle_id)
        // ->where('card_raffle.user_id', '=', null)
        ->select(
            'cards.*',
            'card_raffle.id as card_raffle_id',
        )
        ->orderBy('card_raffle.indice', 'asc');

        // ✅ Si hay user_id, filtrar por él, si no, user_id = null
        if ($userId) {
            $query->where('card_raffle.user_id', '=', $userId);
        } else {
            $query->whereNull('card_raffle.user_id');
        }

        // ->distinct()
        // ->paginate($perPage);

        $AvailableCards = $query->paginate($perPage);

    if ($AvailableCards->isNotEmpty()) {
        return response()->json([
            'message' => 'Cards assigned to this raffle',
            'success' => true,
            'error'   => false,
            'code'    => 'OK-000',
            'date' => date("Y-m-d H:i:s"),
            'data'    => ['Card'=>$AvailableCards]], 200);
    }
    return response()->json([
        'message' => 'No cards assigned to this raffle',
        'success' => false,
        'error' => true,
        'code' => 'ERR-021',
        'date' => date("Y-m-d H:i:s"),
        'data' => ['Card'=>[]]
    ], 404);
}
    
 /**
 * 
 * @param  Request $request
 * @return \Illuminate\Http\Response
 *
 */
   
public function getLastUserRaffle(request $request){

 $lastRaffle = Raffle::where('user_id', $request->user_id)
        ->orderBy('created_at', 'desc')
        ->first();

        if ($lastRaffle) {
            return response()->json([
                'message' => 'Last raffle for this user',
                'success' => true,
                'error'   => false,
                'code'    => 'OK-001',
                'date' => date("Y-m-d H:i:s"),
                'data'    => ['raffle'=>$lastRaffle]], 200);
        }
        else{
            
            return response()->json([
                'message' => 'No raffles found for this user',
                'success' => false,
                'error'   => false,
                'code'    => 'ERR-022',
                'date' => date("Y-m-d H:i:s"),
                'data'    => ['raffle'=>null]], 200);
        }

}


 public function searchRaffleByName(Request $request){
    
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $raffles = Raffle::where('name', 'like', '%' . $request->name . '%')
                        ->orderBy('created_at', 'desc')
                        ->first();
                    

    if ($raffles->isNotEmpty()) {
        return response()->json([
            'message' => 'Raffles found',
            'success' => true,
            'error'   => false,
            'code'    => 'OK-001',
            'date' => date("Y-m-d H:i:s"),
            'data'    => ['raffles'=>$raffles]], 200);
    } else {
        return response()->json([
            'message' => 'No raffles found with that name',
            'success' => false,
            'error'   => true,
            'code'    => 'ERR-023',
            'date' => date("Y-m-d H:i:s"),
            'data'    => ['raffles'=>[]]], 404);
    }
 }

   public function searchRaffle(Request $request)
    {
        // 1. Validación
        $request->validate([
            'user_id' => 'required|integer',
            'search'  => 'required|string|min:1|max:100',
        ]);

        $search = trim($request->input('search'));
        $userId = (int) $request->input('user_id');

        // 2. Decidir si es id o nombre
        //    ctype_digit("") es false, pero ya validamos min:1
        $esId = ctype_digit($search);

        // 3. Construir query
        $query = Raffle::query();

        if ($esId) {
            $query->where('id', (int) $search);
        } else {
            // Búsqueda por nombre (LIKE, case-insensitive según collation)
            $query->where('name', 'like', '%' . $search . '%');
        }

        $raffle = $query->orderBy('created_at', 'desc')->first();

        // 4. No encontrado
        if (!$raffle) {
            return response()->json([
                'message' => $esId
                    ? "No se encontró un sorteo con ID {$search}"
                    : "No se encontró un sorteo con nombre que contenga \"{$search}\"",
                'success' => false,
                'error'   => false,
                'code'    => 'ERR-023',
                'date'    => now()->format('Y-m-d H:i:s'),
                'data'    => ['raffle' => null],
            ], 200);
        }

        // 5. Verificar propiedad
        if ((int) $raffle->user_id !== $userId) {
            return response()->json([
                'message' => 'Este sorteo pertenece a otro usuario',
                'success' => false,
                'error'   => false,
                'code'    => 'ERR-034',
                'date'    => now()->format('Y-m-d H:i:s'),
                'data'    => ['raffle' => null],
            ], 200);
        }

        // 6. Éxito
        return response()->json([
            'message' => $esId
                ? 'Sorteo encontrado por ID'
                : 'Sorteo encontrado por nombre',
            'success' => true,
            'error'   => false,
            'code'    => 'OK-002',
            'date'    => now()->format('Y-m-d H:i:s'),
            'data'    => ['raffle' => $raffle],
        ], 200);
    }

}
