<?php

namespace App\Http\Controllers;

use App\Enums\TransactionType;
use App\Enums\CardType;
use App\Models\Card;
use App\Models\Customer;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CardController extends Controller
{
    public function cardValidate(Request $request): JsonResponse
    {
        // 1. O Middleware 'EnsureMachineIsValid' já garantiu que a máquina é autêntica
        $machine = $request->attributes->get('authenticated_machine');

        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:14'],
        ]);
        $uid = strtoupper($validated['uid']);

        if (Cache::has('cadastro:espera:cliente')) {
            return response()->json([
                'status' => 'REGISTRATION_REQUIRED',
                'message' => 'Use o endpoint de cadastro para vincular o cartao.',
            ], 409);
        }

        $card = Card::where('uid', $uid)
            ->where('status', CardType::ACTIVE->value)
            ->first();

        if (!$card) {
            return response()->json(['status' => 'REJECTED', 'message' => 'Cartão Invalido'], 404);
        }

        $result = DB::transaction(function () use ($card, $machine) {
            $lockedCard = Card::whereKey($card->id)->lockForUpdate()->first();

            if (!$lockedCard || $lockedCard->status !== CardType::ACTIVE) {
                return ['approved' => false, 'message' => 'Cartão Invalido'];
            }

            $currentBalance = (float) $lockedCard->balance;
            $playCost = (float) $machine->cost_per_play;

            if ($currentBalance < $playCost) {
                return ['approved' => false, 'message' => 'Saldo Insuficiente'];
            }

            $newBalance = round($currentBalance - $playCost, 2);
            $lockedCard->balance = $newBalance;
            $lockedCard->save();

            Transaction::create([
                'card_id' => $lockedCard->id,
                'arcade_machine_id' => $machine->id,
                'type' => TransactionType::DEBIT,
                'amount' => $playCost
            ]);

            return ['approved' => true, 'new_balance' => $newBalance];
        });

        if (!$result['approved']) {
            $statusCode = $result['message'] === 'Saldo Insuficiente' ? 402 : 404;

            return response()->json([
                'status' => 'REJECTED',
                'message' => $result['message']
            ], $statusCode);
        }

        return response()->json([
            'status' => 'APPROVED',
            'message' => 'Credito Liberado',
            'new_balance' => $result['new_balance']
        ], 200);
    }

    public function updateStatus(Request $request, Card $card): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:active,blocked,suspended'],
        ]);

        $card->status = CardType::from($validated['status']);
        $card->save();

        return redirect()->back()->with('status_cadastro', [
            'type' => 'success',
            'message' => 'Status do cartao atualizado com sucesso.',
        ]);
    }

    public function credit(Request $request, Card $card): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'gt:0', 'max:999999.99'],
        ]);

        DB::transaction(function () use ($card, $validated): void {
            $lockedCard = Card::whereKey($card->id)->lockForUpdate()->firstOrFail();
            $newBalance = round((float) $lockedCard->balance + (float) $validated['amount'], 2);

            if ($newBalance > 999999.99) {
                throw ValidationException::withMessages([
                    'amount' => 'O saldo do cartao nao pode ultrapassar R$ 999.999,99.',
                ]);
            }

            $lockedCard->balance = $newBalance;
            $lockedCard->save();

            Transaction::create([
                'card_id' => $lockedCard->id,
                'arcade_machine_id' => null,
                'type' => TransactionType::CREDIT,
                'amount' => $validated['amount'],
            ]);
        });

        return redirect()->back()->with('status_cadastro', [
            'type' => 'success',
            'message' => 'Saldo adicionado ao cartao com sucesso.',
        ]);
    }

    public function mode(): JsonResponse
    {
        return response()->json([
            'mode' => Cache::has('cadastro:espera:cliente') ? 'REGISTER_CARD' : 'VALIDATE_PLAY',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'uid' => ['required', 'string', 'max:14'],
        ]);
        $uid = strtoupper($validated['uid']);
        $customerId = Cache::get('cadastro:espera:cliente');

        if (!$customerId) {
            return response()->json(null, 204);
        }

        if (Card::where('uid', $uid)->exists()) {
            return response()->json([
                'status' => 'REJECTED',
                'message' => 'Cartao ja cadastrado',
            ], 409);
        }

        $customer = Customer::find($customerId);

        if (!$customer) {
            Cache::forget('cadastro:espera:cliente');

            return response()->json([
                'status' => 'REJECTED',
                'message' => 'Cliente nao encontrado para vincular o cartao',
            ], 410);
        }

        $customer->cards()->create([
            'uid' => $uid,
            'secure_key' => strtoupper(bin2hex(random_bytes(16))),
            'balance' => 0.00,
            'status' => CardType::ACTIVE,
        ]);

        Cache::forget('cadastro:espera:cliente');

        return response()->json([
            'status' => 'REGISTERED',
            'message' => 'Cartao vinculado com sucesso!',
        ], 201);
    }
}
