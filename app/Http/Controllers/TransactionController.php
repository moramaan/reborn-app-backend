<?php

namespace App\Http\Controllers;

use App\Models\Item;
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

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'item_id' => 'required|string|min:1|exists:items,id',
                'buyer_id' => 'required|int|min:1|exists:users,id',
                'seller_id' => 'required|int|min:1|exists:users,id',
                'price' => 'required|numeric|min:0',
                'transaction_date' => 'required|date',
            ]);

            $item = Item::find($validatedData['item_id']);
            if ($item->state === 'sold') {
                throw new \InvalidArgumentException('Item is already sold');
            }

            $validatedData['id'] = (string) Str::uuid();

            $transaction = Transaction::create($validatedData);

            // Update item state to sold
            $item->state = 'sold';
            $item->save();

            return response()->json(['message' => 'Transaction created', 'transaction' => $transaction], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (ValidationException $e) {
            return response()->json(['message' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to create transaction', 'error' => $e->getMessage()], 500);
        }
    }
}
