<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Ficha;
use App\Models\Card;

class CardSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Card::truncate(); 
        $a=0;
        $array_fichas=array();
        $array_desc=array();
        for ($i=0; $i < 25; $i++) { 
            while ($a <= 24) {
                $f = Ficha::inRandomOrder()->first();
                if (!in_array($f->id, $array_fichas)){           
                    $array_fichas[$a]=$f->id;
                    $array_desc[$a]=$f->name;
                    $a++;
                }
            }
            Card::create([
                'pos01'=> $array_fichas[0],
                'pos02'=> $array_fichas[1],
                'pos03'=> $array_fichas[2],
                'pos04'=> $array_fichas[3],
                'pos05'=> $array_fichas[4],
                'pos06'=> $array_fichas[5],
                'pos07'=> $array_fichas[6],
                'pos08'=> $array_fichas[7],
                'pos09'=> $array_fichas[8],
                'pos10'=> $array_fichas[9],
                'pos11'=> $array_fichas[10],
                'pos12'=> $array_fichas[11],
                'pos13'=> $array_fichas[12],
                'pos14'=> $array_fichas[13],
                'pos15'=> $array_fichas[14],
                'pos16'=> $array_fichas[15],
                'pos17'=> $array_fichas[16],
                'pos18'=> $array_fichas[17],
                'pos19'=> $array_fichas[18],
                'pos20'=> $array_fichas[19],
                'pos21'=> $array_fichas[20],
                'pos22'=> $array_fichas[21],
                'pos23'=> $array_fichas[22],
                'pos24'=> $array_fichas[23],
                'pos25'=> $array_fichas[24],
                'desc_pos01'=> $array_desc[0],
                'desc_pos02'=> $array_desc[1],
                'desc_pos03'=> $array_desc[2],
                'desc_pos04'=> $array_desc[3],
                'desc_pos05'=> $array_desc[4],
                'desc_pos06'=> $array_desc[5],
                'desc_pos07'=> $array_desc[6],
                'desc_pos08'=> $array_desc[7],
                'desc_pos09'=> $array_desc[8],
                'desc_pos10'=> $array_desc[9],
                'desc_pos11'=> $array_desc[10],
                'desc_pos12'=> $array_desc[11],
                'desc_pos13'=> $array_desc[12],
                'desc_pos14'=> $array_desc[13],
                'desc_pos15'=> $array_desc[14],
                'desc_pos16'=> $array_desc[15],
                'desc_pos17'=> $array_desc[16],
                'desc_pos18'=> $array_desc[17],
                'desc_pos19'=> $array_desc[18],
                'desc_pos20'=> $array_desc[19],
                'desc_pos21'=> $array_desc[20],
                'desc_pos22'=> $array_desc[21],
                'desc_pos23'=> $array_desc[22],
                'desc_pos24'=> $array_desc[23],
                'desc_pos25'=> $array_desc[24],
                'comb01' => $array_fichas[0].'|'.$array_fichas[1].'|'.$array_fichas[2].'|'.$array_fichas[3].'|'.$array_fichas[4],
                'comb02' => $array_fichas[5].'|'.$array_fichas[6].'|'.$array_fichas[7].'|'.$array_fichas[8].'|'.$array_fichas[9],
                'comb03' => $array_fichas[10].'|'.$array_fichas[11].'|'.$array_fichas[12].'|'.$array_fichas[13].'|'.$array_fichas[14],
                'comb04' => $array_fichas[15].'|'.$array_fichas[16].'|'.$array_fichas[17].'|'.$array_fichas[18].'|'.$array_fichas[19],
                'comb05' => $array_fichas[20].'|'.$array_fichas[21].'|'.$array_fichas[22].'|'.$array_fichas[23].'|'.$array_fichas[24],
                'comb06' => $array_fichas[0].'|'.$array_fichas[5].'|'.$array_fichas[10].'|'.$array_fichas[15].'|'.$array_fichas[20],
                'comb07' => $array_fichas[1].'|'.$array_fichas[6].'|'.$array_fichas[11].'|'.$array_fichas[16].'|'.$array_fichas[21],
                'comb08' => $array_fichas[2].'|'.$array_fichas[7].'|'.$array_fichas[12].'|'.$array_fichas[17].'|'.$array_fichas[22],
                'comb09' => $array_fichas[3].'|'.$array_fichas[8].'|'.$array_fichas[13].'|'.$array_fichas[18].'|'.$array_fichas[23],
                'comb10' => $array_fichas[4].'|'.$array_fichas[9].'|'.$array_fichas[14].'|'.$array_fichas[19].'|'.$array_fichas[24],
                'comb11' => $array_fichas[0].'|'.$array_fichas[6].'|'.$array_fichas[12].'|'.$array_fichas[18].'|'.$array_fichas[24],
                'comb12' => $array_fichas[4].'|'.$array_fichas[8].'|'.$array_fichas[12].'|'.$array_fichas[16].'|'.$array_fichas[20],
                'comb13' => $array_fichas[0].'|'.$array_fichas[4].'|'.$array_fichas[12].'|'.$array_fichas[20].'|'.$array_fichas[24],
                'comb14' => $array_fichas[2].'|'.$array_fichas[10].'|'.$array_fichas[12].'|'.$array_fichas[14].'|'.$array_fichas[22],
                'combTotal' => implode( '|',$array_fichas),
                'desc_comb01' => $array_desc[0].'|'.$array_desc[1].'|'.$array_desc[2].'|'.$array_desc[3].'|'.$array_desc[4],
                'desc_comb02' => $array_desc[5].'|'.$array_desc[6].'|'.$array_desc[7].'|'.$array_desc[8].'|'.$array_desc[9],
                'desc_comb03' => $array_desc[10].'|'.$array_desc[11].'|'.$array_desc[12].'|'.$array_desc[13].'|'.$array_desc[14],
                'desc_comb04' => $array_desc[15].'|'.$array_desc[16].'|'.$array_desc[17].'|'.$array_desc[18].'|'.$array_desc[19],
                'desc_comb05' => $array_desc[20].'|'.$array_desc[21].'|'.$array_desc[22].'|'.$array_desc[23].'|'.$array_desc[24],
                'desc_comb06' => $array_desc[0].'|'.$array_desc[5].'|'.$array_desc[10].'|'.$array_desc[15].'|'.$array_desc[20],
                'desc_comb07' => $array_desc[1].'|'.$array_desc[6].'|'.$array_desc[11].'|'.$array_desc[16].'|'.$array_desc[21],
                'desc_comb08' => $array_desc[2].'|'.$array_desc[7].'|'.$array_desc[12].'|'.$array_desc[17].'|'.$array_desc[22],
                'desc_comb09' => $array_desc[3].'|'.$array_desc[8].'|'.$array_desc[13].'|'.$array_desc[18].'|'.$array_desc[23],
                'desc_comb10' => $array_desc[4].'|'.$array_desc[9].'|'.$array_desc[14].'|'.$array_desc[19].'|'.$array_desc[24],
                'desc_comb11' => $array_desc[0].'|'.$array_desc[6].'|'.$array_desc[12].'|'.$array_desc[18].'|'.$array_desc[24],
                'desc_comb12' => $array_desc[4].'|'.$array_desc[8].'|'.$array_desc[12].'|'.$array_desc[16].'|'.$array_desc[20],
                'desc_comb13' => $array_desc[0].'|'.$array_desc[4].'|'.$array_desc[12].'|'.$array_desc[20].'|'.$array_desc[24],
                'desc_comb14' => $array_desc[2].'|'.$array_desc[10].'|'.$array_desc[12].'|'.$array_desc[14].'|'.$array_desc[22],
                'desc_combTotal' => implode('|',$array_desc),
                'active' => 1,
                'start_date' => date("Y-m-d H:i:s"),
                ]);
        }
    }
}
