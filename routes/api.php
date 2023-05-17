<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\FichaController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\LevelController;
use App\Http\Controllers\Api\RaffleController;
use App\Http\Controllers\Api\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


Route::resource('Account', AccountController::class)->except([ 'create','edit' ]);



/**********************************   CARD **************************************************/
Route::resource('Card', CardController::class)->except([ 'edit' ]);

Route::post('card/fillcard', [CardController::class, 'fillCard']);
//obtiene todos los cartones 
Route::get('card/getCards', [CardController::class, 'getCards']);
//Onbtirne los cartones dispoinibles a partir de el raffle_id
Route::get ('card/getAvailableCards/{raffle_id}', [CardController::class, 'getAvailableCards']);
//obtiene los cartones disponibles a partir de un grupo
Route::get ('card/getAvailableCardsByGroup/{group_id}', [CardController::class, 'getAvailableCardsByGroup']);

/**********************************   FICHA  ***********************************************/

Route::resource('ficha', FichaController::class)->except([ 'create','edit' ]);

Route::post('ficha/{name}/{active}', [FichaController::class, 'store']);


/**********************************   GROUP  ***********************************************/

Route::resource('Group', GroupController::class)->except([ 'create','edit' ]);

Route::get('Group/getGroup/{group_id}', [GroupController::class, 'getGroup' ]);


/**********************************    USER  **********************************************/

Route::resource('User', RaffleController::class)->except([ 'create','edit' ]);

//Obtiene los grupos en lo que se encuentra el usuario 
Route::get('User/getGrupos/{id}', [UserController::class, 'getGrupos' ]);

//Obtener datos de usuario a partir del firebase
Route::get('User/getUserByFirebase/{firebase_localId}', [UserController::class, 'getUserByFirebase' ]);

//actualiza los datos de firebase a la bd local 
Route::put('User/updateDataFirebase/{firebase_localId}/{firebase_token}/{firebase_last_connection}', [UserController::class, 'updateDataFirebase' ]);

// agregar un usuario a un grupo
Route::put('User/putGroup/{group_id}/{user_id}', [UserController::class, 'putGroup' ]);

// quitar un usuario de un grupo
Route::put('User/putOffGroup/{group_id}/{user_id}', [UserController::class, 'putOffGroup' ]);


/**********************************   LEVEL  ***********************************************/

Route::resource('Level', LevelController::class)->except([ 'create','edit' ]);

/**********************************   RAFFLE  **********************************************/
//obtiene los valores del raffle 
Route::resource('Raffle', RaffleController::class)->except([ 'create','edit' ]);

//obtiene una siguiente ficha en un sorteo si es posible 
Route::get('Raffle/getNewRecord/{raffle_id}', [RaffleController::class, 'getNewRecord']);

//obtiene la siguiente ficha en un sorteo
Route::get('Raffle/newFicha/{raffle_id}', [RaffleController::class, 'newFicha']);

//obtiene las fichas de un sorteo 
Route::get('Raffle/getFichas/{raffle_id}', [RaffleController::class, 'getFichas']);

//obtiene los datos del sorteo
Route::get('Raffle/getRaffle', [RaffleController::class, 'getRaffle']);

//obtiene los datos del sorteo por usuario
Route::get('Raffle/getActiveRafflesByUser/{user_id}', [RaffleController::class, 'getActiveRafflesByUser']); 

//obtiene los datos del sorteo por grupo 
Route::get('Raffle/getActiveRafflesByGroup/{group_id}', [RaffleController::class, 'getActiveRafflesByGroup']); 

//asigna un carton a un usuario y a un sorteo
Route::post('Raffle/putCard/{raffle_id}/{card_id}/{user_id}', [RaffleController::class, 'putCard']);

//obtiene los cartones de un usuario para un sorteo
Route::get('Raffle/getCardsRaffleByUser/{raffle_id}/{user_id}', [RaffleController::class, 'getCardsRaffleByUser']);

//chequea si hay un ganador de linea
Route::post('Raffle/checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);

//chequea si hay ganador de carton lleno
Route::post('Raffle/checkFullWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullWinner']);

//crea un sorteo
Route::post('Raffle/NewRaffle/{admin_retention_amount}/{admin_retention_percent}/{card_amount}/{description}/{group_id}/{maximun_play}/{maximun_user_play}/{minimun_play}/{name}/{percent_full}/{percent_line}/{privacy}/{raffle_type}/{retention_amount}/{retention_percent}/{reward_full}/{reward_line}/{scheduled_date}/{scheduled_hour}/{time_zone}/{user_id}', [RaffleController::class, 'store']);

//actualiza el start_date y start_hour de un sorteo  
Route::post('Raffle/setStart/{raffle_id}/{start_date}/{start_hour}', [RaffleController::class, 'setStart']);

//actualiza el end_date y end_hour de un sorteo  
Route::post('Raffle/endRaffle/{raffle_id}/{end_date}/{end_hour}', [RaffleController::class, 'endRaffle']);

//auto Raffle
Route::get('Raffle/autoRaffle/{raffle_id}', [RaffleController::class, 'autoRaffle']);

//check line Winner
Route::get('Raffle/checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);

//check line Winner
Route::get('Raffle/checkFullW/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullW']);