<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Group;
use App\Models\Level;
use App\Http\Controllers\Api\GroupController;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users= User::all();

        return response()->json(['success'=>true,
                                 'error'=>false,
                                 'code'=>'001-OK',
                                 'message'=>'usuarios encontrados', 
                                 'data'=>['usuarios'=> $users] ],200);
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
            $user->is_admin= $request->is_admin;
            $user->firebase_last_connection = $request->firebase_last_connection;
            $res = $user->save();
            if ($res){
                return response()->json(['error'=> false,
                                        'success'=>true,
                                        'message'=>'User created',
                                        'code'=>'0001-OK',
                                        'data'=>['user'=>$user]], 200);
                                            
            }else{
                return response()->json(['error'=>true,
                                        'success'=> false,
                                        'code'=> 'ERR-033',
                                        'message' => 'Error to create User'], 401);
            }
        } return response()->json(['error'=>true,
                                    'sucess'=> false,
                                    'code'=>'ERR-034',
                                    'message' => 'User duplicated'], 401);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function newUser(Request $request)
    {
         $us = "";
        $us = User::where('email', $request->email)->get();
        if ($us !=""){
            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = $request->pass;
            $user->firebase_localId = $request->firebase_localId;
            $user->firebase_token = $request->firebase_token;
            $user->firebase_last_connection = date("Y-m-d H:i:s");
            $res = $user->save();
            if ($res){
                return response()->json(['error'=>false,
                                        'success'=>true,
                                        'code'=>'001-OK',
                                        'message'=>'User created',
                                        'user'=>$user], 200);
            }else{
                return response()->json(['error'=>true,
                                        'success'=>false,
                                        'code'=>'ERR-032',
                                        'message' => 'Error to create User'], 401);
            }
        } return response()->json(['code'=>'ERR-032',
                                    'error'=>true,
                                    'success'=> false,
                                    'message' => 'User duplicated'], 401);
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
            return response()->json(['error'=>false,
                                    'success'=> true,
                                    'code'=>'001-OK',
                                    'message' => 'User Token updated'], 200);
        } return response()->json(['error' => 'Error to udate User'], 401);
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
        if ($user->Groups != null){
            if (count($user->Groups)>0){
                $resp = array(
                    'date'=> date("Y-m-d H:i:s"),
                    'User'=> $request->id,
                    'Group'=>$user->Groups,
                    'success' => true,
                    'error' => 'not found',
                    'code' => 'ERR-000',
                );
                return  response()-> json($resp,200);
            }   
        }
         return response()->json(['message'=> 'El usuario '.$request->id. ' No tiene Grupos asociados','success' => false,
                                  'error' => true,
                                  'success'=>false,
                                  'code' => 'ERR-016',], 400);  
    }

    public function putGroup(Request $request){

         $g= Group::find($request->group_id);
         $user= User::find($request->user_id);
         $group_user = $user->Groups;
         $i =0;
         foreach ($group_user as $gp ){
            $groups[$i]= $user->id;  
            $i++;
         }
         if ($g!== null && $user !== null ){
 
             if (in_array( $request->user_id , $groups)){
                return response()->json(['error'=> 'El usuario '. $user->name.' ya pertenece al grupo'. $g->name ], 401);
             }
            $res =  $user->Groups()->attach($request->group_id);
            if ( $res !== null) {
                return response()->json(['message'=>$res. ' El usuario '. $user->nombre .' ha sido agregado al Grupo '. $g->name],200);
            }
        } 
         return response()->json(['error'=> 'No se pudo asociar el usuario '.$request->user_id.' al grupo '. $request->group_id, 'grupo'=>$g, 'user'=> $user], 401 );
    }


    public function putOffGroup(Request $request){

        $g= Group::find($request->group_id);
        $user= User::find($request->user_id);
        $group_user = $user->Groups;
        $i =0;
        foreach ($group_user as $gp ){
           $groups[$i]= $user->id;  
           $i++;
        }
        if ($g!== null && $user !== null ){

            if (in_array( $request->user_id , $groups)){
                $res =  $user->Groups()->remove($request->group_id);
                return response()->json(['message'=> 'The User '. $user->name.' ha sido removido del grupo'. $g->name ], 200 );    
            }
           $res =  $user->Groups()->attach($request->group_id);
           if ( $res !== null) {
               return response()->json(['error'=>$res. 'The user '. $user->nombre .' no pertenece al Grupo '. $g->name],401);
            }

       } 
        return response()->json(['error'=> 'No se pudo remover el usuario '.$request->user_id.' al grupo '. $request->group_id, 'grupo'=>$g, 'user'=> $user], 401 );
   }

   public function getUserByFirebase(Request $request){

    $user = User::where('firebase_localId', $request->firebase_localId)->get();
    if ($user!= null){
        return response()->json(['message' => 'usuario found',  'user' => $user], 200);
    }else{
        return response()->json(['error' => 'user not found'], 401);
    }
   }


   public function updateDataFirebase(Request $request){

    $user = User::where('firebase_localId', $request->firebase_localId)->get();
     
    if ($user!= null){
        $user->firebase_token = $request->token;
        $user->firebase_last_connection = $request->last_connection;
        $res = $user->save();
        if ($res){
            return response()->json(['message' => 'Updated user', 'user' => $user], 200);
        }else{
            return response()->json(['error' => 'error updating user'], 401);
        }
    }else{
         return response()->json(['error'=>'user not found'], 401);
    }
   }


   /**
    * $request->user_id
    */
   public function getUserLevel(Request $request){

    $user = User::find($request->id);
    $i = 0;
    
    if (count($user->Levels)>0){
        foreach ($user->Levels as $level ) {
            $levels[$i] = $level->name;
        }
        $resp = array(
            'date'=> date("Y-m-d H:i:s"),
            'User'=> $request->id,
            'Level'=>$levels
        );
        return  response()-> json(['message'=>'level found','level'=>$resp], 200);
    }
     return response()->json(['error'=> 'The User '.$request->id. ' has no associated levels'], 401);
    }

    /**
    * $request->user_id
    */
    public function getUserRoles(Request $request){

        $user = User::find($request->id);
        $i = 0;
        
        if (count($user->Roles)>0){
            foreach ($user->Roles as $role ) {
                $roles[$i] = $roles->name;
            }
            $resp = array(
                'date'=> date("Y-m-d H:i:s"),
                'User'=> $request->id,
                'Role'=>$role
            );
            return  response()-> json(['message'=>'Role found','role'=>$resp], 200);
        }
         return response()->json(['error'=> 'The User '.$request->id. ' has no associated roles'], 401);
    }

   public function getUserEmailConfirm(Request $request, $email){
    
    // Validar que viene el email
    if (empty($email) || $email === 'null') {
        return response()->json([
            'error' => 'Email inválido o vacío'
        ], 400);
    }

    $user = User::where('email', $email)->first();

    if (!$user) {
        return response()->json([
            'error' => "El usuario {$email} no existe"
        ], 401);
    }

    if (empty($user->email_verified_at)) {
        return response()->json([
            'error' => "El usuario {$email} no tiene el email confirmado"
        ], 401);
    }

    $resp = [
        'date'         => date("Y-m-d H:i:s"),
        'User'         => $user->name,
        'EmailConfirm' => $user->email_verified_at,
    ];

    return response()->json([
        'message'     => 'Email confirmado',
        'emailConfirm' => $resp
    ], 200);
}

    /**
    * $request->user_id
    */
    public function getUserPermissions(Request $request){
        $user = User::find($request->id);
        $i = 0;
        $roles=array();
        if (count($user->Roles)>0){
            foreach ($user->Roles as $role ) {
                $roles[$i] = $role->name;
            }
        }
        $i = 0;
        $levels = array();
        if (count($user->Levels)>0){
            foreach ($user->Levels as $level ) {
                $levels[$i] = $level->name;
            }
        }
        $permissions = array(
            'date'=> date("Y-m-d H:i:s"),
            'User'=> $request->id,
            'Level'=>$levels,
            'Role'=>$roles
        );
         return  response()-> json(['message'=>'Permissions found','Permissions'=>$permissions], 200);
        //  return response()->json(['error'=> 'The User '.$request->id. ' has no associated roles'], 401);
    } 

     /**
 * 🔍 ENDPOINT: Verificar si un usuario es admin
 * GET /api/user/checkAdmin?user_id=X
 */
public function checkAdmin(Request $request)
{
    $user = User::find($request->user_id);

    if (!$user) {
        return response()->json(['error' => 'User not found'], 404);
    }

    return response()->json([
        'success' => true,
        'is_admin' => (bool) $user->is_admin,
    ], 200);
}

/**
   * Lista de usuarios paginada
   * @param user     localId del usuario autenticado
   * @param page     número de página (1-based)
   * @param perPage  registros por página
   * @param search   filtro opcional por nombre/email
   */
public function usersList(Request $request, $localId)
    {
        // Validación de parámetros
        $perPage = (int) $request->input('per_page', 10);
        $perPage = max(1, min($perPage, 100)); // límite entre 1 y 100
        $page    = (int) $request->input('page', 1);
        $search  = $request->input('search');

        // Query base
        $query = User::query();

        // Filtro por localId (ajusta a tu lógica real)
        // if ($localId) {
        //     $query->where('firebase_localId', $localId);
        // }

        // Búsqueda opcional
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Orden
        $query->orderBy('id', 'asc');

        // Paginación
        $usuarios = $query->paginate($perPage, ['*'], 'page', $page);
        // Respuesta estandarizada
        return response()->json([
            'success' => true,
            'error'   => false,
            'code'    => '001-OK',
            'message' => 'usuarios encontrados',
            'data'    => [
                'current_page' => $usuarios->currentPage(),
                'last_page'    => $usuarios->lastPage(),
                'per_page'     => $usuarios->perPage(),
                'total'        => $usuarios->total(),
                'from'         => $usuarios->firstItem(),
                'to'           => $usuarios->lastItem(),
                'usuarios'     => $usuarios->items(),
            ],
        ]);
    }
}

