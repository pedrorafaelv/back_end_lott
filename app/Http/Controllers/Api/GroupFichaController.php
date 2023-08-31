<?php

namespace App\Http\Controllers\Api;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Query\JoinClause;
use App\Models\GroupFicha;
use App\Models\FichaGroupficha;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
// use DB;

class GroupFichaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $fichas = DB::table('ficha_groupfichas')
                   ->select('groupficha_id', DB::raw('MIN(ficha_id) as first_ficha_id, MAX(ficha_id) as last_ficha_id'))
                   ->groupBy('groupficha_id');
                    // ->get();

        $descminfichas= DB::table('fichas')
        ->select('fichas.id', 'fichas.name','fichas.image','fichas.description', 'min_fichas.groupficha_id')
        ->joinSub($fichas, 'min_fichas', function (JoinClause $join) {
                        $join->on('fichas.id', '=', 'min_fichas.first_ficha_id'); 
                     });
                    //->get();

        $descmaxfichas= DB::table('fichas')
                    ->select('fichas.id', 'fichas.name','fichas.image','fichas.description', 'max_fichas.groupficha_id')
                    ->joinSub($fichas, 'max_fichas', function (JoinClause $join) {
                        $join->on('fichas.id', '=', 'max_fichas.first_ficha_id'); 
                     });
                    // ->get();


         $GrupoInifichas = DB::table('groupfichas')
                 ->select('groupfichas.id as groupfichas_id', 'groupfichas.name as groupfichas_name', 
                 'groupfichas.description as groupfichas_description', 'min_fichas.id as ficha_id', 
                 'min_fichas.name as ficha_name', 'min_fichas.image as ficha_image', 'min_fichas.description')
                ->joinSub($descminfichas, 'min_fichas', function (JoinClause $join) {
                $join->on('groupfichas.id', '=', 'min_fichas.groupficha_id');
                
                 })
                  ->get();

        $GrupoMaxfichas = DB::table('groupfichas')
                  ->select('groupfichas.id as groupfichas_id', 'groupfichas.name as groupfichas_name', 
                  'groupfichas.description as groupfichas_description', 'max_fichas.id as ficha_id', 
                  'max_fichas.name as ficha_name', 'max_fichas.image as ficha_image', 'max_fichas.description')
                 ->joinSub($descmaxfichas, 'max_fichas', function (JoinClause $join) {
                 $join->on('groupfichas.id', '=', 'max_fichas.groupficha_id');
                 
                  })
                   ->get();
        
        // return response()->json(['groupfichas'=>$Grupofichas, 'descmaxfichas'=> $descmaxfichas, 'descminfichas'=> $descminfichas], 200);
        return response()->json(['GrupoInifichas'=>$GrupoInifichas, 'GrupoMaxfichas'=>$GrupoMaxfichas], 200);
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
     * @param  \App\Models\GroupFicha  $groupFicha
     * @return \Illuminate\Http\Response
     */
    public function show(GroupFicha $groupFicha)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\GroupFicha  $groupFicha
     * @return \Illuminate\Http\Response
     */
    public function edit(GroupFicha $groupFicha)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\GroupFicha  $groupFicha
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, GroupFicha $groupFicha)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\GroupFicha  $groupFicha
     * @return \Illuminate\Http\Response
     */
    public function destroy(GroupFicha $groupFicha)
    {
        //
    }

    public function getGroupFicha(Request $request){

        $Grupoficha= GroupFicha::find($request->groupficha_id);
        // $resp = array(
        //              'groupficha'=> $Grupoficha,
        // );

        return response()->json(['groupficha'=>$Grupoficha], 200);
        // return response()->json($group);
     }
     public function getGroups(){
        dd('groupfichas = ');

        $Grupofichas= GroupFicha::all();
        // $resp = array(
        //              'groupfichas'=> $Grupofichas,
        // );

        return response()->json(['groupfichas'=>$Grupofichas], 200);
        // return response()->json($group);
     }
}
