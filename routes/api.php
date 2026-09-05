 <?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\FichaController;
use App\Http\Controllers\Api\GroupController;
use App\Http\Controllers\Api\GroupFichaController;
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

// Middleware para autenticación
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Rutas de Account
Route::prefix('account')->group(function () {
    Route::resource('/', AccountController::class)->except(['create', 'edit']);
    Route::post('newAccount', [AccountController::class, 'store']);
    Route::post('putAward', [AccountController::class, 'putAward']);
    Route::post('putBet', [AccountController::class, 'putBet']);
    Route::get('getBalanceJson/{user_id}/{currency_code}', [AccountController::class, 'getBalanceJson']);
});

// Rutas de Card
Route::prefix('card')->group(function () {
    Route::resource('/', CardController::class)->except(['edit']);
    Route::post('fillcard', [CardController::class, 'fillCard']);
    Route::get('getCards', [CardController::class, 'getCards']);
    Route::get('getAvailableCards/{raffle_id}', [CardController::class, 'getAvailableCards']);
    Route::get('getAvailableCardsByGroup/{group_id}', [CardController::class, 'getAvailableCardsByGroup']);
});

// Rutas de Ficha
Route::prefix('ficha')->group(function () {
    Route::resource('/', FichaController::class)->except(['create', 'edit']);
    Route::post('{name}/{active}', [FichaController::class, 'store']);
});

// Rutas de Group
Route::prefix('group')->group(function () {
    Route::resource('/', GroupController::class)->except(['create', 'edit']);
    Route::get('getGroup/{group_id}', [GroupController::class, 'getGroup']);
    Route::post('newGroup', [GroupController::class, 'store']); // Cambiado para usar un solo endpoint
});

// Rutas de GroupFicha
Route::prefix('groupFicha')->group(function () {
    Route::resource('/', GroupFichaController::class)->except(['create', 'edit']);
    Route::get('getGroupFichas', [GroupFichaController::class, 'getGroupFichas']);
    Route::get('getGroups/{groupficha_id}', [GroupFichaController::class, 'getGroups']);
    Route::get('getGroupFicha/{groupficha_id}', [GroupFichaController::class, 'getGroupFicha']);
});

// Rutas de User
Route::prefix('user')->group(function () {
    Route::resource('/', UserController::class)->except(['create', 'edit']);
    Route::get('getGrupos/{id}', [UserController::class, 'getGrupos']);
    Route::get('getUserLevel/{id}', [UserController::class, 'getUserLevel']);
    Route::get('getUserRoles/{id}', [UserController::class, 'getUserRoles']);
    Route::get('getUserPermissions/{id}', [UserController::class, 'getUserPermissions']);
    Route::get('getUserEmailConfirm/{email}', [UserController::class, 'getUserEmailConfirm']);
    Route::get('getUserByFirebase/{firebase_localId}', [UserController::class, 'getUserByFirebase']);
    Route::put('updateDataFirebase/{firebase_localId}/{firebase_token}/{firebase_last_connection}', [UserController::class, 'updateDataFirebase']);
    Route::put('putGroup/{group_id}/{user_id}', [UserController::class, 'putGroup']);
    Route::put('putOffGroup/{group_id}/{user_id}', [UserController::class, 'putOffGroup']);
    Route::get('usersList/{user_id}', [UserController::class, 'index']);
    Route::post('newUser', [UserController::class, 'newUser']);
    Route::post('store', [UserController::class, 'store']);
});

// Rutas de Level
Route::prefix('level')->group(function () {
    Route::resource('/', LevelController::class)->except(['create', 'edit']);
});

// Rutas de Raffle
Route::prefix('raffle')->group(function () {
    Route::resource('/', RaffleController::class)->except(['create', 'edit']);
    Route::get('getNewRecord/{raffle_id}', [RaffleController::class, 'getNewRecord']);
    Route::get('newFicha/{raffle_id}', [RaffleController::class, 'newFicha']);
    Route::get('getFichas/{raffle_id}', [RaffleController::class, 'getFichas']);
    Route::get('getRaffleDetails/{raffle_id}', [RaffleController::class, 'getRaffleDetails']);
    Route::get('getActiveRafflesByUser/{user_id}', [RaffleController::class, 'getActiveRafflesByUser']);
    Route::get('getDetailActiveRafflesByUser/{user_id}', [RaffleController::class, 'getDetailActiveRafflesByUser']);
    Route::get('getActiveRafflesByGroup/{group_id}', [RaffleController::class, 'getActiveRafflesByGroup']);
    Route::post('putCard/{raffle_id}/{card_id}/{user_id}', [RaffleController::class, 'putCard']);
    Route::post('cancelBet/{raffle_id}/{card_id}/{user_id}', [RaffleController::class, 'cancelBet']);
    Route::get('getCardsRaffleByUser/{raffle_id}/{user_id}', [RaffleController::class, 'getCardsRaffleByUser']);
    Route::get('getAvailableCardsByRaffle',[RaffleController::class, 'getAvailableCardsByRaffle']);
    // Route::post('checkFullWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullWinner']);
    Route::post('newRaffle', [RaffleController::class, 'store']); // Cambiado para usar un solo endpoint
    Route::post('setStart/{raffle_id}/{start_date}/{start_hour}', [RaffleController::class, 'setStart']);
    Route::post('endRaffle/{raffle_id}/{end_date}/{end_hour}', [RaffleController::class, 'endRaffle']);
    Route::get('autoRaffle/{raffle_id}', [RaffleController::class, 'autoRaffle']);
    Route::get('checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);
    Route::get('checkFullW/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullW']);
});

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });
// Route::resource('Account', AccountController::class)->except([ 'create','edit' ]);

// /**********************************   CARD **************************************************/
// Route::resource('Card', CardController::class)->except([ 'edit' ]);

// Route::post('card/fillcard', [CardController::class, 'fillCard']);
// //obtiene todos los cartones 
// Route::get('card/getCards', [CardController::class, 'getCards']);
// //Onbtirne los cartones dispoinibles a partir de el raffle_id
// Route::get ('card/getAvailableCards/{raffle_id}', [CardController::class, 'getAvailableCards']);
// //obtiene los cartones disponibles a partir de un grupo
// Route::get ('card/getAvailableCardsByGroup/{group_id}', [CardController::class, 'getAvailableCardsByGroup']);

// /**********************************   FICHA  ***********************************************/

// Route::resource('ficha', FichaController::class)->except([ 'create','edit' ]);

// Route::post('ficha/{name}/{active}', [FichaController::class, 'store']);


// /**********************************   GROUP  ***********************************************/

// Route::resource('Group', GroupController::class)->except([ 'create','edit' ]);

// //obtener un grupo 
// Route::get('Group/getGroup/{group_id}', [GroupController::class, 'getGroup' ]);

// //crear un grupo 
// Route::post('Group/NewGroup/{user_id}/{user_admin}/{name}/{description}/{active}/{privacy}/{start_date}/{end_date}', [GroupController::class, 'store' ]);


// /**********************************   GROUPFICHAS  *******************************************/

// Route::resource('GroupFicha', GroupFichaController::class)->except([ 'create','edit' ]);

// Route::get('GroupFicha/getGroupFichas', [GroupFichaController::class, 'getGroupFichas' ]);

// Route::get('GroupFicha/getGroups/{groupficha_id}', [GroupFichaController::class, 'getGroups' ]);

// Route::get('GroupFicha/getGroupFicha/{groupficha_id}', [GroupFichaController::class, 'getGroupFicha' ]);

// /**********************************    USER  **********************************************/

// Route::resource('User', RaffleController::class)->except([ 'create','edit' ]);

// //Obtiene los grupos en lo que se encuentra el usuario 
// Route::get('User/getGrupos/{id}', [UserController::class, 'getGrupos' ]);

// //Obtiene el nivel en que se encuentra el usuario 
// Route::get('User/getUserLevel/{id}', [UserController::class, 'getUserLevel' ]);

// //Obtiene los roles que tiene el usuario 
// Route::get('User/getUserRoles/{id}', [UserController::class, 'getUserRoles' ]); 

// //Obtiene los roles y nivel que tiene el usuario 
// Route::get('User/getUserPermissions/{id}', [UserController::class, 'getUserPermissions' ]);  

// //Obtiene los roles y nivel que tiene el usuario 
// Route::get('User/getUserEmailConfirm/{email}', [UserController::class, 'getUserEmailConfirm' ]); 

// //Obtener datos de usuario a partir del firebase
// Route::get('User/getUserByFirebase/{firebase_localId}', [UserController::class, 'getUserByFirebase' ]);

// //actualiza los datos de firebase a la bd local 
// Route::put('User/updateDataFirebase/{firebase_localId}/{firebase_token}/{firebase_last_connection}', [UserController::class, 'updateDataFirebase' ]);

// // agregar un usuario a un grupo
// Route::put('User/putGroup/{group_id}/{user_id}', [UserController::class, 'putGroup' ]);

// // quitar un usuario de un grupo
// Route::put('User/putOffGroup/{group_id}/{user_id}', [UserController::class, 'putOffGroup' ]);

// // listado de usuarios 
// Route::get('User/usersList/{user_id}', [UserController::class, 'index' ]);

// //crear un Usuario 
// Route::post('User/newUser/{email}/{name}/{pass}/{firebase_localId}/{firebase_token}', [UserController::class, 'newUser' ]);

// //Guardar datos de usuario
// Route::post('User/store/{email}/{name}/{email_verified_at}/{remember_token}/{last_name}/{birth_date}/{document}/{gender}/{phone}/{country}/{state}/{city}/{address}/{firebase_localId}/{firebase_token}/{firebase_last_connection}/{start_date}/{end_date}', [UserController::class, 'store' ]);


// /**********************************   LEVEL  ***********************************************/

// Route::resource('Level', LevelController::class)->except([ 'create','edit' ]);

// /**********************************   RAFFLE  **********************************************/
// //obtiene los valores del raffle 
// Route::resource('Raffle', RaffleController::class)->except([ 'create','edit' ]);

// //obtiene una siguiente ficha en un sorteo si es posible 
// Route::get('Raffle/getNewRecord/{raffle_id}', [RaffleController::class, 'getNewRecord']);

// //obtiene la siguiente ficha en un sorteo
// Route::get('Raffle/newFicha/{raffle_id}', [RaffleController::class, 'newFicha']);

// //obtiene las fichas de un sorteo 
// Route::get('Raffle/getFichas/{raffle_id}', [RaffleController::class, 'getFichas']);

// //obtiene los datos del sorteo
// Route::get('Raffle/getRaffle/{raffle_id}', [RaffleController::class, 'getRaffle']);

// //obtiene los datos del sorteo por usuario
// Route::get('Raffle/getActiveRafflesByUser/{user_id}', [RaffleController::class, 'getActiveRafflesByUser']); 

// //obtiene los datos detallados de los sorteos por usuario
// Route::get('Raffle/getDetailActiveRafflesByUser/{user_id}', [RaffleController::class, 'getDetailActiveRafflesByUser']); 
 
// //obtiene los datos del sorteo por grupo 
// Route::get('Raffle/getActiveRafflesByGroup/{group_id}', [RaffleController::class, 'getActiveRafflesByGroup']); 

// //asigna un carton a un usuario y a un sorteo
// Route::post('Raffle/putCard/{raffle_id}/{card_id}/{user_id}', [RaffleController::class, 'putCard']);

// //obtiene los cartones de un usuario para un sorteo
// Route::get('Raffle/getCardsRaffleByUser/{raffle_id}/{user_id}', [RaffleController::class, 'getCardsRaffleByUser']);

// //chequea si hay un ganador de linea
// Route::post('Raffle/checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);

// chequea si hay ganador de carton lleno-------
// Route::post('Raffle/checkFullWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullWinner']);

// //crea un sorteo
// Route::post('Raffle/NewRaffle/{admin_retention_amount}/{admin_retention_percent}/{card_amount}/{description}/{group_id}/{maximun_play}/{maximun_user_play}/{minimun_play}/{name}/{percent_full}/{percent_line}/{privacy}/{raffle_type}/{retention_amount}/{retention_percent}/{reward_full}/{reward_line}/{scheduled_date}/{scheduled_hour}/{time_zone}/{user_id}', [RaffleController::class, 'store']);

// //actualiza el start_date y start_hour de un sorteo  
// Route::post('Raffle/setStart/{raffle_id}/{start_date}/{start_hour}', [RaffleController::class, 'setStart']);

// //actualiza el end_date y end_hour de un sorteo  
// Route::post('Raffle/endRaffle/{raffle_id}/{end_date}/{end_hour}', [RaffleController::class, 'endRaffle']);

// //auto Raffle
// Route::get('Raffle/autoRaffle/{raffle_id}', [RaffleController::class, 'autoRaffle']);

// //check line Winner
// Route::get('Raffle/checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);

// //check line Winner
// Route::get('Raffle/checkFullW/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullW']);

// // ACOOUNT
// /**********************************   ACCOUNT  **********************************************/

// //Add account
// Route::post('Account/newAccount/{user_id}/{currency_code}/{amount}/{credit}/{credit_promotion}/{deposit}/{withdrawal}/{via}/{description}/{comments}', [AccountController::class, 'store']);

// //Add prize
// Route::post('Account/putAward/{user_id}/{currency_code}/{raffle_id}', [AccountController::class, 'putAward']);

// //Add bet
// Route::post('Account/putBet/{user_id}/{currency_code}/{raffle_id}', [AccountController::class, 'putBet']);

// //get currencyBalance
// Route::get('Account/getBalanceJson/{user_id}/{currency_code}', [AccountController::class, 'getBalanceJson']);