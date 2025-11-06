<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    private function getOrCreateBalance(int $userId): Balance
    {
        $balance = Balance::where('user_id', $userId)->lockForUpdate()->first();

        if (!$balance) {
            try {
                $balance = Balance::create([
                    'user_id' => $userId,
                    'balance' => 0,
                ]);
            } catch (QueryException $e) {
                // Если запись создалась в другой транзакции, получаем её с блокировкой
                if ($e->getSqlState() === '23505') { // Unique violation
                    $balance = Balance::where('user_id', $userId)->lockForUpdate()->firstOrFail();
                } else {
                    throw $e;
                }
            }
        }

        return $balance;
    }

    public function deposit(int $userId, float $amount, ?string $comment = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::findOrFail($userId);

            $balance = $this->getOrCreateBalance($userId);

            $balance->increment('balance', $amount);

            Transaction::create([
                'user_id' => $userId,
                'type' => 'deposit',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            $balance->refresh();

            return [
                'user_id' => $userId,
                'balance' => $balance->balance,
            ];
        });
    }

    public function withdraw(int $userId, float $amount, ?string $comment = null): array
    {
        return DB::transaction(function () use ($userId, $amount, $comment) {
            $user = User::findOrFail($userId);

            $balance = Balance::where('user_id', $userId)->lockForUpdate()->first();

            if (!$balance || $balance->balance < $amount) {
                abort(409, 'Insufficient funds');
            }

            $balance->decrement('balance', $amount);

            Transaction::create([
                'user_id' => $userId,
                'type' => 'withdraw',
                'amount' => $amount,
                'comment' => $comment,
            ]);

            $balance->refresh();

            return [
                'user_id' => $userId,
                'balance' => $balance->balance,
            ];
        });
    }

    public function transfer(int $fromUserId, int $toUserId, float $amount, ?string $comment = null): array
    {
        return DB::transaction(function () use ($fromUserId, $toUserId, $amount, $comment) {
            User::findOrFail($fromUserId);
            User::findOrFail($toUserId);

            $fromBalance = Balance::where('user_id', $fromUserId)->lockForUpdate()->first();

            if (!$fromBalance || $fromBalance->balance < $amount) {
                abort(409, 'Insufficient funds');
            }

            $toBalance = $this->getOrCreateBalance($toUserId);

            $fromBalance->decrement('balance', $amount);
            $toBalance->increment('balance', $amount);

            Transaction::create([
                'user_id' => $fromUserId,
                'type' => 'transfer_out',
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $toUserId,
            ]);

            Transaction::create([
                'user_id' => $toUserId,
                'type' => 'transfer_in',
                'amount' => $amount,
                'comment' => $comment,
                'related_user_id' => $fromUserId,
            ]);

            $fromBalance->refresh();
            $toBalance->refresh();

            return [
                'from_user_id' => $fromUserId,
                'to_user_id' => $toUserId,
                'from_balance' => $fromBalance->balance,
                'to_balance' => $toBalance->balance,
            ];
        });
    }

    public function getBalance(int $userId): array
    {
        $user = User::findOrFail($userId);

        $balance = Balance::where('user_id', $userId)->first();

        return [
            'user_id' => $userId,
            'balance' => $balance ? $balance->balance : 0,
        ];
    }
}
