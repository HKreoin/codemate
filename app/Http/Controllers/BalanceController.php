<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositRequest;
use App\Http\Requests\TransferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Services\BalanceService;
use Illuminate\Http\JsonResponse;

class BalanceController extends Controller
{
    public function __construct(
        private readonly BalanceService $balanceService
    ) {
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $result = $this->balanceService->deposit(
            $request->validated()['user_id'],
            $request->validated()['amount'],
            $request->validated()['comment'] ?? null
        );

        return response()->json($result, 200);
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $result = $this->balanceService->withdraw(
            $request->validated()['user_id'],
            $request->validated()['amount'],
            $request->validated()['comment'] ?? null
        );

        return response()->json($result, 200);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $result = $this->balanceService->transfer(
            $validated['from_user_id'],
            $validated['to_user_id'],
            $validated['amount'],
            $validated['comment'] ?? null
        );

        return response()->json($result, 200);
    }

    public function balance(int $userId): JsonResponse
    {
        $result = $this->balanceService->getBalance($userId);

        return response()->json($result, 200);
    }
}
