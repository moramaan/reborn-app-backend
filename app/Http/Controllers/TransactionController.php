<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class TransactionController extends Controller
{
    public function index()
    {
        try {
            $transactions = Transaction::all();
            return response()->json($transactions);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve transactions', 'error' => $e->getMessage()], 500);
        }
    }

    public function show($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            return response()->json($transaction);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Transaction not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve transaction', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();
            return response()->json($transaction);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Transaction not found', 'error-message' => $e->getMessage()], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to delete transaction', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'item_id' => 'required|int|min:1',
                'buyer_id' => 'required|int|min:1|exists:users,id',
                'seller_id' => 'required|int|min:1|exists:users,id',
                'price' => 'required|numeric|min:0',
                'transaction_date' => 'required|date',
            ]);

            $validatedData['id'] = (string) Str::uuid();

            $transaction = Transaction::create($validatedData);

            return response()->json(['message' => 'Transaction created', 'transaction' => $transaction], 201);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create transaction', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            $validatedData = $request->validate([
                'id' => 'required|uuid|exists:transactions,id',
                'item_id' => 'required|int|min:1',
                'buyer_id' => 'required|int|min:1|exists:users,id',
                'seller_id' => 'required|int|min:1|exists:users,id',
                'price' => 'required|numeric|min:0',
                'transaction_date' => 'required|date',
            ]);

            $transaction->update($validatedData);

            return response()->json(['message' => 'Transaction updated', 'transaction' => $transaction]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Transaction not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to update transaction', 'error' => $e->getMessage()], 500);
        }
    }
}
