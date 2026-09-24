<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApproveWithdrawalRequest;
use App\Http\Requests\Admin\CompleteWithdrawalRequest;
use App\Http\Requests\Admin\RejectWithdrawalRequest;
use App\Http\Resources\WithdrawalDetailResource;
use App\Http\Resources\WithdrawalResource;
use App\Models\Withdrawal;
use App\Services\WithdrawalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WithdrawalController extends Controller
{
    public function __construct(
        private WithdrawalService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        $data = $this->service->list($request->all());

        return response()->json([
            'success' => true,
            'data' => [
                'withdrawals' => WithdrawalResource::collection($data->items()),
                'pagination' => [
                    'total'        => $data->total(),
                    'per_page'     => $data->perPage(),
                    'current_page' => $data->currentPage(),
                    'last_page'    => $data->lastPage(),
                ],
                'stats' => $this->service->dashboardStats(),
            ],
        ]);
    }

    public function show(Withdrawal $withdrawal): JsonResponse
    {
        $withdrawal->load(['user', 'via', 'account', 'approvedBy', 'rejectedBy', 'completedBy', 'logs.admin']);

        return response()->json([
            'success' => true,
            'data' => new WithdrawalDetailResource($withdrawal),
        ]);
    }

    public function logs(Withdrawal $withdrawal): JsonResponse
    {
        $logs = $withdrawal->logs()->with('admin:id,name')->get();

        return response()->json([
            'success' => true,
            'data' => $logs->map(fn ($log) => [
                'id'         => $log->id,
                'action'     => $log->action->value,
                'notes'      => $log->notes,
                'metadata'   => $log->metadata,
                'admin'      => $log->admin ? ['id' => $log->admin->id, 'name' => $log->admin->name] : null,
                'created_at' => $log->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json(['success' => true, 'data' => $this->service->dashboardStats()]);
    }

    public function detailedStats(Request $request): JsonResponse
    {
        return response()->json(['success' => true, 
                                 'code'=>'001_OK',
                                 'error'=> false,
                                 'data' => $this->service->detailedStats($request->all())]);
    }

    public function approve(ApproveWithdrawalRequest $request, Withdrawal $withdrawal): JsonResponse
    {
        $updated = $this->service->approve($withdrawal, $request->user()->id, $request->input('admin_notes'));

        return response()->json([
            'success' => true,
            'message' => 'Retiro aprobado correctamente',
            'data'    => new WithdrawalDetailResource($updated),
        ]);
    }

    public function reject(RejectWithdrawalRequest $request, Withdrawal $withdrawal): JsonResponse
    {
        $updated = $this->service->reject(
            $withdrawal,
            $request->user()->id,
            $request->input('rejection_reason'),
            $request->input('admin_notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Retiro rechazado y monto reembolsado',
            'data'    => new WithdrawalDetailResource($updated),
        ]);
    }

    public function complete(CompleteWithdrawalRequest $request, Withdrawal $withdrawal): JsonResponse
    {
        $updated = $this->service->complete(
            $withdrawal,
            $request->user()->id,
            $request->input('transaction_reference'),
            $request->input('admin_notes')
        );

        return response()->json([
            'success' => true,
            'message' => 'Retiro completado correctamente',
            'data'    => new WithdrawalDetailResource($updated),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->service->export($request->all()),
        ]);
    }
    public function myWhitdrawals(resquest $request){

     
    }
}