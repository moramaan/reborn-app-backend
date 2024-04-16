<?php

# this will be my user controller

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Item;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;

class ItemController extends Controller
{
    public function index()
    {
        $items = Item::all();
        return response()->json($items);
    }

    public function show($id)
    {
        try {
            $item = Item::findOrFail($id);
            return response()->json($item);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve item', 'error' => $e->getMessage()], 500);
        }
    }

    public function search(Request $request)
    {
        try {

            $query = Item::query();
            if ($request->has('filters')) {
                $filters = $request->input('filters');

                foreach ($filters as $filter) {
                    if (isset($filter['column']) && isset($filter['value'])) {
                        $column = $filter['column'];
                        $value = $filter['value'];

                        // Handle price range filter separately
                        if ($column === 'price') {
                            if (isset($filter['min'])) {
                                $query->where('price', '>=', $filter['min']);
                            }
                            if (isset($filter['max'])) {
                                $query->where('price', '<=', $filter['max']);
                            }
                        } else {
                            $value = $filter['value'];

                            if (in_array($column, app(Item::class)->getFillable())) {
                                $query->where($column, $value);
                            }
                        }
                    }
                    // Check for orderBy and order
                    if (isset($filter['orderBy'])) {
                        $orderBy = $filter['orderBy'];
                        $order = isset($filter['order']) && strtolower($filter['order']) === 'desc' ? 'desc' : 'asc';
                        $query->orderBy($orderBy, $order);
                    }
                }
            }

            $results = $query->get()->toArray();

            return response()->json($results);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to search items', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $item = Item::findOrFail($id);
            $item->delete();
            return response()->json($item);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Item not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete item', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|min:4|max:255',
                'description' => 'required|string|min:4|max:255',
                'price' => 'required|numeric|min:0',
                'state' => 'nullable|in:available,sold,reserved',
                'publish_date' => 'required|date',
                'user_id' => 'required|int|min:1',
            ]);

            $item = Item::create($validatedData);

            return response()->json(['message' => 'Item created', 'item' => $item], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create item', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json(['error' => 'Invalid item id'], 400);
            }

            $item = Item::findOrFail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|min:4|max:255',
                'description' => 'required|string|min:4|max:255',
                'price' => 'required|numeric|min:0',
                'state' => 'nullable|in:available,sold,reserved',
                'publish_date' => 'required|date',
                'user_id' => 'required|int|min:1',
            ]);

            $item->update($validatedData);

            return response()->json(['message' => 'Item updated', 'item' => $item]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Item not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item', 'error' => $e->getMessage()], 500);
        }
    }
}
