<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    //
    public function index(request $request){
        $query = Currency::query()->ordered();
 
        // Filtro por estado: ?active=1
        if ($request->has('active')) {
            $query->where('is_active', $request->boolean('active'));
        }

         // Filtro por crypto: ?active=1
        if ($request->has('crypto')) {
            $query->where('is_crypto', $request->boolean('active'));
        }   

        // Búsqueda por name/code: ?search=deposito
        if ($request->filled('search')) {
            $term = $request->input('search');
            $query->where(function ($q) use ($term) {
                $q->where('name', 'like', "%{$term}%")
                  ->orWhere('code', 'like', "%{$term}%");
            });
        }

        $resp = $query->get();
        if ($resp){
            return response()->json(['success'=>true,
                                     'error'=>false,
                                     'code'=> 'OK-001',
                                     'message'=> 'currencies found',
                                     'data'=>['currencies'=>$resp]], 200);
        }
        return response()->json(['success'=>true,
                'error'=>false,
                'code'=> 'ERR-040',
                'message'=> 'currencies Not found',]);
    

    }

    public function canBeSeeByUser(){

        $resp =Currency::canBeSeeByUser()->get();
        if ($resp){

            return response()->json([
                'success'=>true,
                'error'=>false,
                'code'=> 'OK-001',
                'message'=> 'currencies found',
                'data'=>['currencies'=>$resp]],200);
            
        }
        return response()->json(['success'=>true,
                'error'=>false,
                'code'=> 'ERR-041',
                'message'=> 'currencies Not found',], 404);
    }
}
