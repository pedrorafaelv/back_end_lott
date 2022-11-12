<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Http\Controllers\Api\GroupController;


class UserController extends Controller
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
    }
    
    public function getGrupos(Request $request){

         $user = User::find($request->id);
        $i = 0;
        // dd($user->Groups);
        if (count($user->Groups)>0){
            $resp = array(
                'date'=> date("Y-m-d H:i:s"),
                'User'=> $request->id,
                'Group'=>$user->Groups
            );
            return  response()-> json($resp);
        }
         return response()->json(['message'=> 'El usuario '.$request->id. ' No tiene Grupos asociados'], 500);  
    }

    public function putGroup(Request $request){

         $g= Group::find($request->group_id);
         $user= User::find($request->user_id);
         $group_user = $user->Groups;
        //  print_r($group_user);
         $i =0;
         foreach ($group_user as $gp ){
            $groups[$i]= $user->id;  
            $i++;
         }
         if ($g!== null && $user !== null ){
 
             if (in_array( $request->user_id , $groups)){
                return response()->json(['message'=> 'El usuario '. $user->name.' ya pertenece al grupo'. $g->name ], 500 );    
             }
            $res =  $user->Groups()->attach($request->group_id);
            if ( $res !== null) {
                return response()->json(['message'=>$res. ' El usuario '. $user->nombre .' ha sido agregado al Grupo '. $g->name],200);
            }

        } 
         return response()->json(['message'=> 'No se pudo asociar el usuario '.$request->user_id.' al grupo '. $request->group_id, 'grupo'=>$g, 'user'=> $user], 500 );
    }

    public function putOffGroup(Request $request){

        $g= Group::find($request->group_id);
        $user= User::find($request->user_id);
        $group_user = $user->Groups;
       //  print_r($group_user);
        $i =0;
        foreach ($group_user as $gp ){
           $groups[$i]= $user->id;  
           $i++;
        }
        if ($g!== null && $user !== null ){

            if (in_array( $request->user_id , $groups)){
                $res =  $user->Groups()->remove($request->group_id);
                return response()->json(['message'=> 'El usuario '. $user->name.' ha sido removido del grupo'. $g->name ], 500 );    
            }
           $res =  $user->Groups()->attach($request->group_id);
           if ( $res !== null) {
               return response()->json(['message'=>$res. ' El usuario '. $user->nombre .' no pertenece al Grupo '. $g->name],200);
            }

       } 
        return response()->json(['message'=> 'No se pudo remover el usuario '.$request->user_id.' al grupo '. $request->group_id, 'grupo'=>$g, 'user'=> $user], 500 );
   }
}
