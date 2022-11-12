<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\card;
use Illuminate\Http\Request;
use App\Models\Ficha;

class CardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
         //  TODO: OBTENER LOS CARD DEPENDIENDO DEL GRUPO  DE FICHAS
        $cards= Card::get();
        // dd($cards);
        $i =0;
        foreach ($cards as $key => $card) {
             $fichas= explode('|', $card->combTotal);
             $r=0;
             foreach ($fichas as $key => $ficha) {
                    $f[$r]= Ficha::find($ficha);
                    $r++;                
            }
           $result[$i]= array('card_id' => $card->id,
                              'fichas' => $f);
            $i++;
        }
        $resp = array(
            'Date'=> date("Y-m-d H:i:s"),
            'Group'=> 1,
            'Card'=>$cards,
            'Records'=>$result
        );
        return response()->Json($resp);
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\card  $card
     * @return \Illuminate\Http\Response
     */
    public function show(Card $Card)
    {
        // return response()->json( Card::find($card));
         $card= Card::find($Card);
         $fichas= explode('|', $Card->combTotal);
         $i=0;
         foreach ($fichas as $key => $ficha) {
             $f[$i]= Ficha::find($ficha);
             $i++;                
         }
         $result= array('card' => $Card,
                       'fichas' => $f,
                       'description' => explode('|', $Card->desc_combTotal)
                     );
        return response()->Json($result);
    }

    

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\card  $card
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, card $card)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\card  $card
     * @return \Illuminate\Http\Response
     */
    public function destroy(card $card)
    {
        //
    }

    public function getCards(Card $card){

        $cards= Card::get();
        $i =0;
        // foreach ($cards as $key => $card) {
        //     $result[$i] = $card;
        //     $i++;
        // }
        dd($cards);
         $resp= array(
             'date'=> date("Y-m-d H:i:s"),
             'card'=>$cards,
             'group'=> 1             
         );
        return response()->Json($resp);
     }

    public function fillCard(Request $request){
        $card= new card();
        $a=0;
        $array_fichas = array();
        $array_desc   = array();
        while ($a <= 24) {
            $f = Ficha::inRandomOrder()->first();
            if (!in_array($f->id, $array_fichas)){           
                $array_fichas[$a]=$f->id;
                 $array_desc[$a]=$f->image;
                $a ++ ;
            }
        }
        $card->pos01 = intval($array_fichas[0]);
        $card->pos02 = intval($array_fichas[1]);
        $card->pos03 = $array_fichas[2];
        $card->pos04 = $array_fichas[3];
        $card->pos05 = $array_fichas[4];
        $card->pos06 = $array_fichas[5];
        $card->pos07 = $array_fichas[6];
        $card->pos08 = $array_fichas[7];
        $card->pos09 = $array_fichas[8];
        $card->pos10 = $array_fichas[9];
        $card->pos11 = $array_fichas[10];
        $card->pos12 = $array_fichas[11];
        $card->pos13 = $array_fichas[12];
        $card->pos14 = $array_fichas[13];
        $card->pos15 = $array_fichas[14];
        $card->pos16 = $array_fichas[15];
        $card->pos17 = $array_fichas[16];
        $card->pos18 = $array_fichas[17];
        $card->pos19 = $array_fichas[18];
        $card->pos20 = $array_fichas[19];
        $card->pos21 = $array_fichas[20];
        $card->pos22 = $array_fichas[21];
        $card->pos23 = $array_fichas[22];
        $card->pos24 = $array_fichas[23];
        $card->pos25 = $array_fichas[24];
        $card->desc_pos01 = $array_desc[0];
        $card->desc_pos02 = $array_desc[1];
        $card->desc_pos03 = $array_desc[2];
        $card->desc_pos04 = $array_desc[3];
        $card->desc_pos05 = $array_desc[4];
        $card->desc_pos06 = $array_desc[5];
        $card->desc_pos07 = $array_desc[6];
        $card->desc_pos08 = $array_desc[7];
        $card->desc_pos09 = $array_desc[8];
        $card->desc_pos10 = $array_desc[9];
        $card->desc_pos11 = $array_desc[10];
        $card->desc_pos12 = $array_desc[11];
        $card->desc_pos13 = $array_desc[12];
        $card->desc_pos14 = $array_desc[13];
        $card->desc_pos15 = $array_desc[14];
        $card->desc_pos16 = $array_desc[15];
        $card->desc_pos17 = $array_desc[16];
        $card->desc_pos18 = $array_desc[17];
        $card->desc_pos19 = $array_desc[18];
        $card->desc_pos20 = $array_desc[19];
        $card->desc_pos21 = $array_desc[20];
        $card->desc_pos22 = $array_desc[21];
        $card->desc_pos23 = $array_desc[22];
        $card->desc_pos24 = $array_desc[23];
        $card->desc_pos25 = $array_desc[24];
        $card->comb01 = $array_fichas[0].'|'.$array_fichas[1].'|'.$array_fichas[2].'|'.$array_fichas[3].'|'.$array_fichas[4];
        $card->comb02 = $array_fichas[5].'|'.$array_fichas[6].'|'.$array_fichas[7].'|'.$array_fichas[8].'|'.$array_fichas[9];
        $card->comb03 = $array_fichas[10].'|'.$array_fichas[11].'|'.$array_fichas[12].'|'.$array_fichas[13].'|'.$array_fichas[14];
        $card->comb04 = $array_fichas[15].'|'.$array_fichas[16].'|'.$array_fichas[17].'|'.$array_fichas[18].'|'.$array_fichas[19];
        $card->comb05 = $array_fichas[20].'|'.$array_fichas[21].'|'.$array_fichas[22].'|'.$array_fichas[23].'|'.$array_fichas[24];
        $card->comb06 = $array_fichas[0].'|'.$array_fichas[5].'|'.$array_fichas[10].'|'.$array_fichas[15].'|'.$array_fichas[20];
        $card->comb07 = $array_fichas[1].'|'.$array_fichas[6].'|'.$array_fichas[11].'|'.$array_fichas[16].'|'.$array_fichas[21];
        $card->comb08 = $array_fichas[2].'|'.$array_fichas[7].'|'.$array_fichas[12].'|'.$array_fichas[17].'|'.$array_fichas[22];
        $card->comb09 = $array_fichas[3].'|'.$array_fichas[8].'|'.$array_fichas[13].'|'.$array_fichas[18].'|'.$array_fichas[23];
        $card->comb10 = $array_fichas[4].'|'.$array_fichas[9].'|'.$array_fichas[14].'|'.$array_fichas[19].'|'.$array_fichas[24];
        $card->comb11 = $array_fichas[0].'|'.$array_fichas[6].'|'.$array_fichas[12].'|'.$array_fichas[18].'|'.$array_fichas[24];
        $card->comb12 = $array_fichas[4].'|'.$array_fichas[8].'|'.$array_fichas[12].'|'.$array_fichas[16].'|'.$array_fichas[20];
        $card->comb13 = $array_fichas[0].'|'.$array_fichas[4].'|'.$array_fichas[12].'|'.$array_fichas[20].'|'.$array_fichas[24];
        $card->comb14 = $array_fichas[2].'|'.$array_fichas[10].'|'.$array_fichas[12].'|'.$array_fichas[14].'|'.$array_fichas[22];
        $card->combTotal = implode( '|', $array_fichas);
        $card->desc_comb01 = $array_desc[0].'|'.$array_desc[1].'|'.$array_desc[2].'|'.$array_desc[3].'|'.$array_desc[4];
        $card->desc_comb02 = $array_desc[5].'|'.$array_desc[6].'|'.$array_desc[7].'|'.$array_desc[8].'|'.$array_desc[9];
        $card->desc_comb03 = $array_desc[10].'|'.$array_desc[11].'|'.$array_desc[12].'|'.$array_desc[13].'|'.$array_desc[14];
        $card->desc_comb04 = $array_desc[15].'|'.$array_desc[16].'|'.$array_desc[17].'|'.$array_desc[18].'|'.$array_desc[19];
        $card->desc_comb05 = $array_desc[20].'|'.$array_desc[21].'|'.$array_desc[22].'|'.$array_desc[23].'|'.$array_desc[24];
        $card->desc_comb06 = $array_desc[0].'|'.$array_desc[5].'|'.$array_desc[10].'|'.$array_desc[15].'|'.$array_desc[20];
        $card->desc_comb07 = $array_desc[1].'|'.$array_desc[6].'|'.$array_desc[11].'|'.$array_desc[16].'|'.$array_desc[21];
        $card->desc_comb08 = $array_desc[2].'|'.$array_desc[7].'|'.$array_desc[12].'|'.$array_desc[17].'|'.$array_desc[22];
        $card->desc_comb09 = $array_desc[3].'|'.$array_desc[8].'|'.$array_desc[13].'|'.$array_desc[18].'|'.$array_desc[23];
        $card->desc_comb10 = $array_desc[4].'|'.$array_desc[9].'|'.$array_desc[14].'|'.$array_desc[19].'|'.$array_desc[24];
        $card->desc_comb11 = $array_desc[0].'|'.$array_desc[6].'|'.$array_desc[12].'|'.$array_desc[18].'|'.$array_desc[24];
        $card->desc_comb12 = $array_desc[4].'|'.$array_desc[8].'|'.$array_desc[12].'|'.$array_desc[16].'|'.$array_desc[20];
        $card->desc_comb13 = $array_desc[0].'|'.$array_desc[4].'|'.$array_desc[12].'|'.$array_desc[20].'|'.$array_desc[24];
        $card->desc_comb14 = $array_desc[2].'|'.$array_desc[10].'|'.$array_desc[12].'|'.$array_desc[14].'|'.$array_desc[22];
        $card->desc_combTotal = implode( '|', $array_desc);
        $card->active = 1;
        $card->start_date = date("Y-m-d H:i:s");
        $res = $card->save();
        
         if ($res) {
              $i=1;
             foreach ($array_fichas as $f){
                 $card->Fichas()->attach($f, ['posicion' => $i]);
                 $i++;
             }
              return response()->json($card, 201);
           }
         return response()->json(['message' => 'Error to get cards'], 500);    
    }
}
