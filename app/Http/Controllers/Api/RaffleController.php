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
use App\Http\Controllers\Api\FichaController;

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
            $res = "";
            if ($res){
                return response()->json($raffle, 200);
            }else{
                return response()->json(['message' => 'Error to create Raffle', 'group_id'=>$request->group_id], 500);
            }
        }
        return response()->json(['message' => 'Error to create Raffle, Raffle(s) in progress', 'open_raffle'=>$open_raffle,'group_id'=>$request->group_id], 500);
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
        if (count($fichas) > 0){
            foreach($fichas as $ficha){
                $f[$i]= $ficha->name;
                $i++;
            }
            return response()->json(['Raffle'=> $Raffle, 'ArrFichas'=> $f, 'Fichas'=> Raffle::find($request->raffle_id)->Fichas]); 
        }
         return response()->json(['message' => 'Error to get Raffle data'], 500);
    }
    

    public function getRaffle(){

        $raffle = new Raffle();
        // $raffle->name = 'sorteo';
        // $raffle->description = '';
        // $res = $raffle->save();
         return response()->json($res, 200);
    }


/**
 * $request->raffle_id
 * $request->card_id
 * $request->user_id
 */
    public function putCard(Request $request){

        $raffle =  Raffle::find($request->raffle_id);
         $i = 0;
        $cards_raffle = $raffle->Cards;
        foreach( $cards_raffle as $card){
            $cards[$i]= $card->id;  
            $i++;
        }
        if (!is_array($cards)){
            $cards = array();
        }
        $i= count($cards) + 1;
        if (!in_array($request->card_id, $cards)){           
           $res = $raffle->Cards()->attach($request->card_id, [ 'raffle_id'=>$request->raffle_id, 
                                                                'user_id'=>$request->user_id, 
                                                                'indice'=> $i, 
                                                                'created_at'=>date("Y-m-d H:i:s"), 
                                                                'updated_at'=> date("Y-m-d H:i:s")]);
        }
        if ($res){
            return response()->json( $raffle->Cards);
        } else{
            return response()->json(['message' => 'Error to create Raffle'], 500);
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
        ->select('cards.id','cards.combTotal' ,'check_fulls.nro_faltas as faltas' , 'check_fulls.faltantes as faltantes', 'check_fulls.id as checkFull_id')
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
     * @param raffle_id $raffle_id
     * @return \Illuminate\Http\Response
     * raffle_id
     * ficha_id
     */
    public function checkLineWinner(Request $request){
        $lines = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
        ->join('check_cards', 'cards.id', '=', 'check_cards.card_id')
        ->where('card_ficha.ficha_id', '=', $request->ficha_id)
        ->where('card_raffle.raffle_id', '=', $request->raffle_id )
        ->where('check_cards.raffle_id', '=', $request->raffle_id )
        ->select('cards.id', 'check_cards.nro_faltas as faltas' , 'check_cards.faltantes as faltantes', 'check_cards.id as checkCards_id')
        ->orderBy('faltas')
        ->get();
        $i=0;
         //print_r($lines);
         if (count($lines) > 0){
            foreach($lines as $line){
                if ($line->faltas ===1){
                    $lineWinner[$i] = $line;
                    $i++;
                }elseif(count ($lineWinner) === 0) 
                {   
                    $lineWinner = array();
                    $array_faltantes = explode('|', $line->faltantes);
                    $checkCards->find($line->checkCard_id);
                    $checkCards->nro_faltas = $line->faltas -1;
                    if (($request->ficha_id= array_search($request->ficha_id, $array_faltantes)) !== false) {
                        unset($array_faltantes[$request->ficha_id]);
                    }
                    $checkCards-> implode('|',$array_faltantes);
                    $checkCards->save(); 
                } 
            }
            return response()->json(['winner'=>$lineWinner], 200);
         }

             
         $cardswith4 = DB::table('cards')
                       ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
                       ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
                       ->leftJoin('check_cards', 'cards.id', '=', 'check_cards.card_id')
                       ->where('card_ficha.ficha_id', '=', $request->ficha_id)
                       ->where('card_raffle.raffle_id', '=', $request->raffle_id )
                       ->where('check_cards.raffle_id','=', $request->raffle_id)
                       ->where('check_cards.card_id', '=' , null)
                       ->select('cards.*')
                       ->get();
        // print_r($cardswith4);          
         foreach ($cardswith4 as $c){
             $card = Card:: find($c->id);
             $checkC = new checkCard();
             $checkC->id_card = $card->id;
             $checkC->id_raffle = $request->raffle_id;
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

        //    // se verifica cada linea de cada carton que participa 
            $faltan = checkCard::where('card_id', $c->id)->where('raffle_id', $request->raffle_id)->get();
             $j =0;
            foreach($faltan as $f ){
              $l[$j] = $f->linea;
              $j++;
            }  
            print_r($l);
           if (in_array($request->ficha_id, $array_comb01 ) && !in_array(1,$l)){    
                $checkC->linea =1;
                $checkC->combinacion ='comb01';              
                if (($clave = array_search($request->ficha_id, $array_comb01)) !== false) {              
                    unset($array_comb01[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb01 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb02) && !in_array(2, $l)){    
                $checkC->linea =2;
                $checkC->combinacion ='comb02';              
                if (($clave = array_search($request->ficha_id, $array_comb02)) !== false) {          
                    unset($array_comb02[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb02 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb03 ) && !in_array(3, $l)){    
                $checkC->linea =3;
                $checkC->combinacion ='comb03';              
                if (($clave = array_search($request->ficha_id, $array_comb03)) !== false) {             
                    unset($array_comb03[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb03 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb04 ) && !in_array(4, $l)){    
                $checkC->linea =4;
                $checkC->combinacion ='comb04';              
                if (($clave = array_search($request->ficha_id, $array_comb04)) !== false) {           
                    unset($array_comb04[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb04 );
                $checkC->save();
            }
           
            if (in_array($request->ficha_id, $array_comb05 )&& !in_array(5, $l)){    
                $checkC->linea =5;
                $checkC->combinacion ='comb05';              
                if (($clave = array_search($request->ficha_id, $array_comb05)) !== false) {         
                    unset($array_comb05[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb05 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb06 )&& !in_array(6, $l)){    
                $checkC->linea =6;
                $checkC->combinacion ='comb06';              
                if (($clave = array_search($request->ficha_id, $array_comb06)) !== false) {             
                    unset($array_comb06[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb06 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb07 ) && !in_array(7, $l)){    
                $checkC->linea =7;
                $checkC->combinacion ='comb07';              
                if (($clave = array_search($request->ficha_id, $array_comb07)) !== false) {             
                    unset($array_comb07[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb07 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb08 ) && !in_array(8, $l)){    
                $checkC->linea =8;
                $checkC->combinacion ='comb08';              
                if (($clave = array_search($request->ficha_id, $array_comb08)) !== false) {              
                    unset($array_comb08[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb08 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb09 ) && !in_array(9, $l)){    
                $checkC->linea =9;
                $checkC->combinacion ='comb09';              
                if (($clave = array_search($request->ficha_id, $array_comb09)) !== false) {             
                    unset($array_comb09[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb09 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb10 ) && !in_array(10, $l)){    
                $checkC->linea =10;
                $checkC->combinacion ='comb10';              
                if (($clave = array_search($request->ficha_id, $array_comb10)) !== false) {             
                    unset($array_comb10[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb10 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb11 ) && !in_array(11, $l)){    

                $checkC->linea =11;
                $checkC->combinacion ='comb11';              
                if (($clave = array_search($request->ficha_id, $array_comb11)) !== false) {             
                    unset($array_comb11[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb11 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb12 ) && !in_array(12, $l)){    
                $checkC->linea =12;
                $checkC->combinacion ='comb12';              
                if (($clave = array_search($request->ficha_id, $array_comb12)) !== false) {               
                    unset($array_comb12[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb12 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb13 ) && !in_array(13, $l)){    
                $checkC->linea =13;
                $checkC->combinacion ='comb13';              
                if (($clave = array_search($request->ficha_id, $array_comb13)) !== false) {               
                    unset($array_comb13[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb13 );
                $checkC->save();
            }

            if (in_array($request->ficha_id, $array_comb14 ) && !in_array(14, $l)){    
                $checkC->linea =14;
                $checkC->combinacion ='comb14';              
                if (($clave = array_search($request->ficha_id, $array_comb14)) !== false) {              
                    unset($array_comb14[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb14 );
                $checkC->save();
            }
        }
        return response()->json(['resp'=>'No hay ganadores'], 200);
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
            return response()->json($raffle, 200);
        }else{
            return response()->json(['message' => 'Error to save Raffle', 'group_id'=> $request->raffle_id], 500);
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
            return response()->json($raffle, 200);
        }else{
            return response()->json(['message' => 'Error to save Raffle', 'group_id'=> $request->raffle_id], 500);
        }
    }
     /**
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     * raffle_id
     * user_id
     */
     public function getCardsRaffle(Request $request){

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
        return response()->json(['Cards'=>$userCardsRaffle, 'Fichas'=> $fichas]);
     }
   
    /**
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     *
     */

     public function newFicha(Raffle $raffle){
        
        $f = Ficha::inRandomOrder()->first();
        $i =0;
         //obtener las fichas de la tabla fichas_raffles
        $fichas_raffle = Raffle::find($raffle->id)->Fichas;
     //    echo '$r = <pre>'. $raffle->id; print_r($fichas_raffle); echo '</pre>';
     //    $fichas_raffle =$r->Fichas;
        //echo '$fichas_raffle = ';print_r($fichas_raffle);
        if(count($fichas_raffle)>0){
            foreach ($fichas_raffle as  $ficha){
                $fichas[$i]= $ficha->id;  
                $i++;
            }
        }else{
            $fichas = array();
        } 
        $i= count($fichas) + 1;
     //    $raffle =new Raffle();
        if (!in_array($f->id, $fichas)){           
           $res =  $raffle->Fichas()->attach($f, ['raffle_id'=>$raffle->id,
                                                  'indice'=> $i, 
                                                  'created_at'=>date("Y-m-d H:i:s"), 
                                                  'updated_at'=> date("Y-m-d H:i:s")]);
        }
        return $f; 
     }
      
     /**
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     *
     */
    function autoRaffle(Request $request){
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
     * 
     * @param  Request $request
     * @return \Illuminate\Http\Response
     *
     */
    function getNewRecord(Request $request){
        //Obtener el sorteo
        $r = Raffle::find($request->raffle_id); 
        //verificar que el sorteo no esté cerrado
        if ($r->winner == NULL && $r->full_winner == NULL && $r->end_date ==NULL){
            //obtener nueva ficha
            $ficha = $this->newFicha($r);
            //verificar que el sorteo tenga ganador de linea o lleno
            if ($r->reward_line == 1 && $r->winner == ""){
                //verificar ganador de línea
                $lineWinner = $this->checkLine($r->id, $ficha->id);
                // guardar el line winner en el registro del raffle
                if($lineWinner != ""){
                    $r->winner = $lineWinner;
                    $r->save();
                    $this->endRaffle($request);
                }
            }
            if ($r->reward_full == 1){
                //verificar si tiene ganador lleno
                $fullWinner = $this->checkFullW($r->id, $ficha->id);
                if($fullWinner != ""){
                    $r->full_winner =  $fullWinner;
                    $this->endRaffle($request); 
                    return response()->json (['raffle' =>$r, 'ficha'=> $ficha]);
                }
               // guardar el line winner en el registro del raffle
            }
            return response()->json(['raffle'=>$r, 'ficha'=> $ficha]);  
        }else{
            return response()->json(['message' => 'Error to get a new Record']);
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
    public function checkFullW( $raffle_id, $ficha_id){
     
        $checkFull = new CheckFull();
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
                    if (($ficha_id= array_search($ficha_id, $array_faltantes)) !== false) {
                        unset($array_faltantes[$ficha_id]);
                    }
                    $checkFull-> implode('|', $array_faltantes);
                    $checkFull->save(); 
                } 
            }
            return $fullWinner;
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
    public function checkLine( $raffle_id, $ficha_id ){
        $lines = DB::table('cards')
        ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
        ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
        ->join('check_cards', 'cards.id', '=', 'check_cards.card_id')
        ->where('card_ficha.ficha_id', '=', $ficha_id)
        ->where('card_raffle.raffle_id', '=', $raffle_id )
        ->where('check_cards.raffle_id', '=', $raffle_id )
        ->select('cards.id', 'check_cards.nro_faltas as faltas' , 'check_cards.faltantes as faltantes', 'check_cards.id as checkCards_id')
        ->orderBy('faltas')
        ->get();
        $i=0;
        //  var_dump($lines);
         if (count($lines) > 0){
            foreach($lines as $line){
                if ($line->faltas ===1){
                    $lineWinner[$i] = $line;
                    $i++;
                }elseif(count ($lineWinner) === 0) 
                {   
                    $lineWinner = array();
                    $array_faltantes = explode('|', $line->faltantes);
                    $checkCards->find($line->checkCard_id);
                    $checkCards->nro_faltas = $line->faltas -1;
                    if (($ficha_id= array_search($ficha_id, $array_faltantes)) !== false) {
                        unset($array_faltantes[$ficha_id]);
                    }
                    $checkCards-> implode('|',$array_faltantes);
                    $checkCards->save(); 
                } 
            }
            // guardar ganador de linea en raffle
            //  $raffle = Raffle::find($raffle_id);
            //  $raffle->winner = $lineWinner;
            //  $raffle->save();  
            return $lineWinner;
         }        
         $cardswith4 = DB::table('cards')
                       ->join('card_raffle', 'cards.id', '=', 'card_raffle.card_id')
                       ->join('card_ficha', 'cards.id', '=', 'card_ficha.card_id')
                       ->leftJoin('check_cards', 'cards.id', '=', 'check_cards.card_id')
                       ->where('card_ficha.ficha_id', '=', $ficha_id)
                       ->where('card_raffle.raffle_id', '=', $raffle_id )
                       ->where('check_cards.raffle_id','=', $raffle_id)
                       ->where('check_cards.card_id', '=' , null)
                       ->select('cards.*')
                       ->get();
        // print_r($cardswith4);          
         foreach ($cardswith4 as $c){
             $card = Card:: find($c->id);
             $checkC = new checkCard();
             $checkC->id_card = $card->id;
             $checkC->id_raffle = $raffle_id;
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

        //    // se verifica cada linea de cada carton que participa 
            $faltan = checkCard::where('card_id', $c->id)->where('raffle_id', $raffle_id)->get();
             $j =0;
            foreach($faltan as $f ){
              $l[$j] = $f->linea;
              $j++;
            }  
            print_r($l);
           if (in_array($ficha_id, $array_comb01 ) && !in_array(1,$l)){    
                $checkC->linea =1;
                $checkC->combinacion ='comb01';              
                if (($clave = array_search($ficha_id, $array_comb01)) !== false) {              
                    unset($array_comb01[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb01 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb02) && !in_array(2, $l)){    
                $checkC->linea =2;
                $checkC->combinacion ='comb02';              
                if (($clave = array_search($ficha_id, $array_comb02)) !== false) {          
                    unset($array_comb02[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb02 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb03 ) && !in_array(3, $l)){    
                $checkC->linea =3;
                $checkC->combinacion ='comb03';              
                if (($clave = array_search($ficha_id, $array_comb03)) !== false) {             
                    unset($array_comb03[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb03 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb04 ) && !in_array(4, $l)){    
                $checkC->linea =4;
                $checkC->combinacion ='comb04';              
                if (($clave = array_search($ficha_id, $array_comb04)) !== false) {           
                    unset($array_comb04[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb04 );
                $checkC->save();
            }
           
            if (in_array($ficha_id, $array_comb05 )&& !in_array(5, $l)){    
                $checkC->linea =5;
                $checkC->combinacion ='comb05';              
                if (($clave = array_search($ficha_id, $array_comb05)) !== false) {         
                    unset($array_comb05[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb05 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb06 )&& !in_array(6, $l)){    
                $checkC->linea =6;
                $checkC->combinacion ='comb06';              
                if (($clave = array_search($ficha_id, $array_comb06)) !== false) {             
                    unset($array_comb06[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb06 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb07 ) && !in_array(7, $l)){    
                $checkC->linea =7;
                $checkC->combinacion ='comb07';              
                if (($clave = array_search($ficha_id, $array_comb07)) !== false) {             
                    unset($array_comb07[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb07 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb08 ) && !in_array(8, $l)){    
                $checkC->linea =8;
                $checkC->combinacion ='comb08';              
                if (($clave = array_search($ficha_id, $array_comb08)) !== false) {              
                    unset($array_comb08[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb08 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb09 ) && !in_array(9, $l)){    
                $checkC->linea =9;
                $checkC->combinacion ='comb09';              
                if (($clave = array_search($ficha_id, $array_comb09)) !== false) {             
                    unset($array_comb09[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb09 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb10 ) && !in_array(10, $l)){    
                $checkC->linea =10;
                $checkC->combinacion ='comb10';              
                if (($clave = array_search($ficha_id, $array_comb10)) !== false) {             
                    unset($array_comb10[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb10 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb11 ) && !in_array(11, $l)){    

                $checkC->linea =11;
                $checkC->combinacion ='comb11';              
                if (($clave = array_search($ficha_id, $array_comb11)) !== false) {             
                    unset($array_comb11[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb11 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb12 ) && !in_array(12, $l)){    
                $checkC->linea =12;
                $checkC->combinacion ='comb12';              
                if (($clave = array_search($ficha_id, $array_comb12)) !== false) {               
                    unset($array_comb12[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb12 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb13 ) && !in_array(13, $l)){    
                $checkC->linea =13;
                $checkC->combinacion ='comb13';              
                if (($clave = array_search($ficha_id, $array_comb13)) !== false) {               
                    unset($array_comb13[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb13 );
                $checkC->save();
            }

            if (in_array($ficha_id, $array_comb14 ) && !in_array(14, $l)){    
                $checkC->linea =14;
                $checkC->combinacion ='comb14';              
                if (($clave = array_search($ficha_id, $array_comb14)) !== false) {              
                    unset($array_comb14[$clave]);
                }
                $checkC->faltantes= implode('|',$array_comb14 );
                $checkC->save();
            }
        }
        return false;
    }
}
