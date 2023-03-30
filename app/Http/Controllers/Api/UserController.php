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
         $us = "";
        $us = User::where('email', $request->email)->get();
        if ($us !=""){
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->email_verified_at = $request->email_verified_at;
            $user->remember_token = $request->remember_token;
            $user->last_name = $request->last_name;
            $user->birth_date = $request->birth_date;
            $user->document = $request->document;
            $user->gender = $request->gender;
            $user->phone = $request->phone;
            $user->phone_verified_at = $request->phone_verified_at;
            $user->country = $request->country;
            $user->state = $request->state;
            $user->city = $request->city;
            $user->address = $request->address;
            $user->firebase_localId = $request->firebase_localId;
            $user->firebase_token = $request->firebase_token;
            $user->firebase_last_connection = $request->firebase_last_connection;
            $res = $user->save();
            if ($res){
                return response()->json($user, 200);
            }else{
                return response()->json(['message' => 'Error to create User'], 401);
            }
        } return response()->json(['message' => 'User duplicated'], 401);
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

    public function updateToken(Request $request, $id)
    {
        $us = User::where('email', $request->email)->get();
        if ($us){
            $us->remember_token = $request->remember_token;
            $res= $us->save();
            return response()->json(['message' => 'User Token updated'], 200);
        } return response()->json(['message' => 'Error to udate User'], 401);
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
         return response()->json(['message'=> 'El usuario '.$request->id. ' No tiene Grupos asociados'], 400);  
    }

    public function putGroup(Request $request){

         $g= Group::find($request->group_id);
         $user= User::find($request->user_id);
         $group_user = $user->Groups;
        //  echo '<pre>';print_r($group_user); echo '</pre>';
         $i =0;
         foreach ($group_user as $gp ){
            $groups[$i]= $user->id;  
            $i++;
         }
         if ($g!== null && $user !== null ){
 
             if (in_array( $request->user_id , $groups)){
                return response()->json(['message'=> 'El usuario '. $user->name.' ya pertenece al grupo'. $g->name ], 401 );    
             }
            $res =  $user->Groups()->attach($request->group_id);
            if ( $res !== null) {
                return response()->json(['message'=>$res. ' El usuario '. $user->nombre .' ha sido agregado al Grupo '. $g->name],200);
            }
        } 
         return response()->json(['message'=> 'No se pudo asociar el usuario '.$request->user_id.' al grupo '. $request->group_id, 'grupo'=>$g, 'user'=> $user], 400 );
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
                return response()->json(['message'=> 'El usuario '. $user->name.' ha sido removido del grupo'. $g->name ], 200 );    
            }
           $res =  $user->Groups()->attach($request->group_id);
           if ( $res !== null) {
               return response()->json(['message'=>$res. ' El usuario '. $user->nombre .' no pertenece al Grupo '. $g->name],400);
            }

       } 
        return response()->json(['message'=> 'No se pudo remover el usuario '.$request->user_id.' al grupo '. $request->group_id, 'grupo'=>$g, 'user'=> $user], 401 );
   }

   public function getUserByFirebase(Request $request){

    $user = User::where('firebase_localId', $request->firebase_localId)->get();
    // echo 'user en getUserByFirebase back end<pre>'; print_r($user); echo '</pre> hasta aqui';
    if ($user!= null){
        return response()->json(['message' => 'usuario encontrado',  'user' => $user], 200);
    }else{
        return response()->json(['message' => 'usuario no encontrado'], 401);
    }
   }

   public function updateDataFirebase(Request $request){

    $user = User::where('firebase_localId', $request->firebase_localId)->get();
    
    if ($user!= null){
        $user->firebase_token = $request->token;
        $user->firebase_last_connection = $request->last_connection;
        $res = $user->save();
        if ($res){
            return response()->json(['message' => 'Updated user', 'user'->$user], 200);
        }else{
            return response()->json(['message' => 'error updating user'], 401);
        }
    }else{
         return response()->json(['message'=>'user not found'], 401);
    }
   }

}
