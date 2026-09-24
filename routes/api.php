 <?php
use App\Http\Controllers\Controller;
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
use App\Http\Controllers\Admin\WithdrawalController;
use App\Http\Controllers\Admin\UserLevelController;

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
    Route::get('usersList/{user_id}', [UserController::class, 'usersList']);
    Route::post('newUser', [UserController::class, 'newUser']);
    Route::post('store', [UserController::class, 'store']);
    Route::get('checkAdmin', [UserController::class, 'checkAdmin']);

});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/level', [UserLevelController::class, 'show']);
    Route::get('/user/level/permissions', [UserLevelController::class, 'permissions']);
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
    Route::get('getAvailableCardsByRaffle/{raffle_id}', [RaffleController::class, 'getAvailableCardsByRaffle']);
    // Route::post('checkFullWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullWinner']);
    Route::post('newRaffle', [RaffleController::class, 'store']); // Cambiado para usar un solo endpoint
    Route::post('setStart/{raffle_id}/{start_date}/{start_hour}', [RaffleController::class, 'setStart']);
    Route::post('endRaffle/{raffle_id}/{end_date}/{end_hour}', [RaffleController::class, 'endRaffle']);
    Route::get('autoRaffle/{raffle_id}', [RaffleController::class, 'autoRaffle']);
    Route::get('checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);
    Route::get('checkFullW/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullW']);
});



/** ***********************************   Account ****************************************************/
Route::prefix('account')->group(function () {
    Route::get('/summary', [AccountController::class, 'getSummary']);
    Route::get('/transactions', [AccountController::class, 'getTransactions']);
    Route::post('/checkBet', [AccountController::class, 'checkBet']);
    Route::post('/putBet', [AccountController::class, 'putBet']);
    Route::post('/putAward', [AccountController::class, 'putAward']);
    Route::post('/store', [AccountController::class, 'store']);
    Route::post('/requestWithdrawal', [AccountController::class, 'requestWithdrawal']);
    Route::post('/cancelWithdrawal', [AccountController::class, 'cancelWithdrawal']);
    });
    
    
    Route::middleware('auth:sanctum')->prefix('admin')->group(function () {
        
        Route::prefix('withdrawals')->middleware('admin')->group(function () {
            Route::get('/',                [WithdrawalController::class, 'index']);
            Route::get('/export',          [WithdrawalController::class, 'export']);
            Route::get('/stats',           [WithdrawalController::class, 'stats']);
            Route::get('/stats/detailed',  [WithdrawalController::class, 'detailedStats']);
            Route::get('/myWithdrawals', [WithdrawalController::class, 'myWithdrawals']);
            Route::get('/{withdrawal}',    [WithdrawalController::class, 'show']);
            Route::get('/{withdrawal}/logs', [WithdrawalController::class, 'logs']);
            Route::post('/{withdrawal}/approve',  [WithdrawalController::class, 'approve']);
            Route::post('/{withdrawal}/reject',   [WithdrawalController::class, 'reject']);
            Route::post('/{withdrawal}/complete', [WithdrawalController::class, 'complete']);
    });
});

