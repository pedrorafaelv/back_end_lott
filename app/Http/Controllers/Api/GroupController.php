<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
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
        $gr = "";
        $gr = Group::where('name', $request->name)->get();
        if ($gr !=""){
            $group = new Group();
            $group->user_id = $request->user_id;
            $group->user_admin = $request->user_admin;
            $group->name = $request->name;
            $group->description = $request->description;
            $group->active = $request->active;
            $group->privacy = $request->privacy;
            $group->start_date = $request->start_date;
            $group->end_date = $request->end_date;
            $res = $group->save();
            if ($res){
                return response()->json(['message'=>'Group created','group'=>$group], 200);
            }else{
                return response()->json(['error' => 'Error to create Group'], 401);
            }
        } return response()->json(['error' => 'Group duplicated'], 401);
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $group
     * @return \Illuminate\Http\Response
     */
    public function show(Group $group)
    {
      
        return response()->json($group);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function edit(Group $group)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Group $group)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
    public function destroy(Group $group)
    {
        //
    }
    
    /**
     *  specified resource from storage.
     *
     * @param  \App\Models\Group  $group
     * @return \Illuminate\Http\Response
     */
     public function getGroup(Request $request){

        $Grupo= Group::find($request->group_id);
        $resp = array(
                     'group'=> $Grupo,
        );
        return response()->json($resp);
     }
    
}
