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
use App\Http\Controllers\Admin\DepositRequestController;
use App\Http\Controllers\Admin\UserLevelController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Api\ViaController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\TermsController;
use App\Http\Middleware\CaptureRequestContext;
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

// Rutas públicas de auth
Route::prefix('auth')->group(function () {
    Route::post('/exchange', [AuthController::class, 'exchange']);
});

Route::middleware('auth:sanctum')->group(function () {

    // ============================================
    // RUTAS ACCOUNT
    // ============================================

    Route::prefix('account')->group(function () {
        Route::resource('/', AccountController::class)->except(['create', 'edit']);
        Route::post('newAccount', [AccountController::class, 'store']);
        Route::post('putAward', [AccountController::class, 'putAward']);
        Route::post('putBet', [AccountController::class, 'putBet']);
        Route::get('getBalanceJson/{user_id}/{currency_code}', [AccountController::class, 'getBalanceJson']);
        Route::get('/summary', [AccountController::class, 'getSummary']);
        Route::get('/transactions', [AccountController::class, 'getTransactions']);
        Route::post('/checkBet', [AccountController::class, 'checkBet']);
        Route::post('/store', [AccountController::class, 'store']);
        Route::post('/requestWithdrawal', [AccountController::class, 'requestWithdrawal']);
        Route::post('/cancelWithdrawal', [AccountController::class, 'cancelWithdrawal']);
        Route::post('/requestDeposit', [AccountController::class, 'requestDeposit']);
        Route::post('/cancelDeposit', [AccountController::class, 'cancelDeposit']);
        Route::get('/myDeposits', [AccountController::class, 'myDeposits']);
        Route::get('/getTotals', [AccountController::class, 'getTotals']);
    });

    // ============================================
    // RUTAS CARD
    // ============================================

    Route::prefix('card')->group(function () {
        Route::resource('/', CardController::class)->except(['edit']);
        Route::post('fillcard', [CardController::class, 'fillCard']);
        Route::get('getCards', [CardController::class, 'getCards']);
        Route::get('getAvailableCards/{raffle_id}', [CardController::class, 'getAvailableCards']);
        Route::get('getAvailableCardsByGroup/{group_id}', [CardController::class, 'getAvailableCardsByGroup']);
    });

    // ============================================
    // RUTAS FICHA
    // ============================================

    Route::prefix('ficha')->group(function () {
        Route::resource('/', FichaController::class)->except(['create', 'edit']);
        Route::post('{name}/{active}', [FichaController::class, 'store']);
    });
    
    // ============================================
    // RUTAS GROUP
    // ============================================

    Route::prefix('group')->group(function () {
        Route::resource('/', GroupController::class)->except(['create', 'edit']);
        Route::get('getGroup/{group_id}', [GroupController::class, 'getGroup']);
        Route::post('newGroup', [GroupController::class, 'store']); // Cambiado para usar un solo endpoint
    });
    
    
    // ============================================
    // RUTAS GROUPFICHA
    // ============================================

    Route::prefix('groupFicha')->group(function () {
        Route::resource('/', GroupFichaController::class)->except(['create', 'edit']);
        Route::get('getGroupFicha/{id}', [GroupFichaController::class, 'show']);
        Route::get('getGroups/', [GroupFichaController::class, 'getGroups']);
        Route::get('getGroupFicha/{groupficha_id}', [GroupFichaController::class, 'getGroupFicha']);
    });

    
    // ============================================
    // RUTAS USER
    // ============================================

    Route::prefix('user')->group(function () {
        Route::get('getGrupos/{id}', [UserController::class, 'getGrupos']);
        Route::get('getUserLevel/{id}', [UserController::class, 'getUserLevel']);
        Route::get('getUserRoles/{id}', [UserController::class, 'getUserRoles']);
        Route::get('getUserData', [UserController::class, 'getUserData']);
        Route::get('getUserPermissions/{id}', [UserController::class, 'getUserPermissions']);
        Route::put('putGroup/{group_id}/{user_id}', [UserController::class, 'putGroup']);
        Route::put('putOffGroup/{group_id}/{user_id}', [UserController::class, 'putOffGroup']);
        Route::get('usersList/{user_id}', [UserController::class, 'usersList']);
        Route::get('checkAdmin', [UserController::class, 'checkAdmin']);
        Route::get('/user/level/permissions', [UserLevelController::class, 'permissions']);
        Route::resource('/', UserController::class)->except(['create', 'edit']);
       
    });

    Route::middleware([ 'admin'])->prefix('user')->group(function () {
        Route::get('/', [UserController::class, 'index']);
        Route::get('usersList/{user_id}', [UserController::class, 'usersList']);
        
    });


    // ============================================
    // RUTAS RAFFLE
    // ============================================

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
        Route::post('/raffle', [RaffleController::class, 'store']);
        Route::get('getCardsRaffleByUser/{raffle_id}/{user_id}', [RaffleController::class, 'getCardsRaffleByUser']);
        Route::get('getAvailableCardsByRaffle/{raffle_id}', [RaffleController::class, 'getAvailableCardsByRaffle']);
        Route::post('newRaffle', [RaffleController::class, 'store']); // Cambiado para usar un solo endpoint
        Route::post('setStart/{raffle_id}/{start_date}/{start_hour}', [RaffleController::class, 'setStart']);
        Route::post('endRaffle/{raffle_id}/{end_date}/{end_hour}', [RaffleController::class, 'endRaffle']);
        Route::get('autoRaffle/{raffle_id}', [RaffleController::class, 'autoRaffle']);
        Route::get('checkLineWinner/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkLineWinner']);
        Route::get('checkFullW/{raffle_id}/{ficha_id}', [RaffleController::class, 'checkFullW']);
        Route::get('getLastUserRaffle', [RaffleController::class, 'getLastUserRaffle']);
        Route::get('searchRaffle', [RaffleController::class, 'searchRaffle']);
        Route::get ('scheduler/status',  [RaffleController::class, 'schedulerStatus']);
        Route::post('scheduler/toggle',  [RaffleController::class, 'schedulerToggle']);
        Route::post('scheduler/trigger', [RaffleController::class, 'schedulerTrigger']);
         Route::post('scheduler/config',  [RaffleController::class, 'schedulerConfig']);

    });

    // Rutas protegidas de auth
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me',      [AuthController::class, 'me']);
        // Route::post('/exchange',[AuthController::class, 'exchange']);
    });

    Route::prefix('vias')->group(function () {
        // Listados
        Route::get('/', [ViaController::class, 'index']);
        Route::get('/credits', [ViaController::class, 'credits']);
        Route::get('/debits', [ViaController::class, 'debits']);
        Route::get('/neutral', [ViaController::class, 'neutral']);
        Route::get('/creditsCanBeSeeByUser', [ViaController::class, 'creditsCanBeSeeByUser']);
        Route::get('/debitsCanBeSeeByUser', [ViaController::class, 'debitsCanBeSeeByUser']);
        Route::get('/code/{code}', [ViaController::class, 'showByCode']);

        // CRUD
        Route::get('/{via}', [ViaController::class, 'show']);
   
    });

    // ─────────────────────────────────────────────────────────────
    // RUTAS TERMS
    // ─────────────────────────────────────────────────────────────
    Route::middleware([ CaptureRequestContext::class])->prefix('terms')->group(function () {
        Route::get('/status', [TermsController::class, 'status']);
        Route::get('/current', [TermsController::class, 'current']);
        Route::post('/accept', [TermsController::class, 'accept']);
    });

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


// ============================================
// RUTAS ADMIN - DEPÓSITOS
// ============================================
Route::middleware('auth:sanctum')->prefix('admin')->group(function () {

    Route::prefix('admin/deposits')->middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('/', [DepositRequestController::class, 'index']);
        Route::get('/stats', [DepositRequestController::class, 'stats']);
        Route::get('/export', [DepositRequestController::class, 'export']);
        Route::post('/manual', [DepositRequestController::class, 'createManual']);
        Route::get('/{id}', [DepositRequestController::class, 'show']);
        Route::post('/{id}/approve', [DepositRequestController::class, 'approve']);
        Route::post('/{id}/reject', [DepositRequestController::class, 'reject']);
        Route::post('/{id}/complete', [DepositRequestController::class, 'complete']);
    });
});

// ============================================
// BUSCAR USUARIOS (Admin)
// ============================================
Route::get('/admin/users/search', [UserController::class, 'searchUsers'])
    ->middleware(['auth:sanctum', 'admin']);


    
// ─────────────────────────────────────────────────────────────
// Endpoints PÚBLICOS (sin autenticación) — para el registro
// ─────────────────────────────────────────────────────────────
    // ============================================
    // RUTAS DE TASA DE CAMBIO (Exchange Rate)
    // ============================================   

Route::prefix('exchange-rate')->group(function () {
    // Consultar la tasa desde la BD (rápido, para frontend)
    Route::get('/latest', [ExchangeRateController::class, 'latest']);
    
    // Forzar scraping y actualizar (más lento, usar con moderación)
    Route::post('/refresh', [ExchangeRateController::class, 'refresh']);
});

Route::prefix('currencies')->group(function () {
    // Listados
    Route::get('/', [CurrencyController::class, 'index']);
    Route::get('/canBeSeeByUser', [CurrencyController::class, 'canBeSeeByUser']);
});

 Route::prefix('auth')->group(function () {
    Route::post('/exchange', [AuthController::class, 'exchange'])->middleware('throttle:20,1');
    Route::post('/register', [AuthController::class, 'register'])
        ->middleware([CaptureRequestContext::class, 'throttle:5,1']);
});

Route::prefix('terms')->middleware('throttle:60,1')->group(function () {
    Route::get('/version', [TermsController::class, 'publicVersion']);
});
