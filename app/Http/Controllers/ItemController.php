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
        $items = Item::listAvailableItems();
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
            $query = Item::query()->whereNot('state', 'sold');

            $filters = $request->input('filters', []);
            if (!is_array($filters)) {
                throw new \InvalidArgumentException('Invalid filters format');
            }

            foreach ($filters as $filter) {
                if (isset($filter['column'], $filter['value'])) {
                    $column = $filter['column'];
                    $value = $filter['value'];

                    if ($column === 'price') {
                        if (isset($filter['min'])) {
                            $query->where('price', '>=', $filter['min']);
                        }
                        if (isset($filter['max'])) {
                            $query->where('price', '<=', $filter['max']);
                        }
                    } elseif ($column === 'name') {
                        $query->where('name', 'like', "%$value%");
                    } else {
                        if (!in_array($column, app(Item::class)->getFillable())) {
                            throw new \InvalidArgumentException("Column '$column' is not searchable");
                        }
                        if ($column === 'state' && $value === 'sold') {
                            throw new \InvalidArgumentException("Column '$column' cannot be used to search for sold items");
                        } 
                        $query->where($column, $value);
                    }
                }

                if (isset($filter['orderBy'])) {
                    $orderBy = $filter['orderBy'];
                    $order = strtolower($filter['order'] ?? '') === 'desc' ? 'desc' : 'asc';
                    $query->orderBy($orderBy, $order);
                }
            }

            $results = $query->get()->toArray();

            return response()->json($results);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to search items'], 500);
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
                'state' => 'nullable|in:available,reserved', // create items as sold don't make sense
                'condition' => 'required|int|min:0|max:2',
                'publish_date' => 'required|date',
                'user_id' => 'required|int|min:1|exists:users,id',
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

            if (!$item->canBeUpdated()) {
                return response()->json(['error' => 'Sold items cannot be updated'], 400);
            }

            $validatedData = $request->validate([
                'name' => 'required|string|min:4|max:255',
                'description' => 'required|string|min:4|max:255',
                'price' => 'required|numeric|min:0',
                'state' => 'nullable|in:available,sold,reserved',
                'condition' => 'required|int|min:0|max:2',
                'publish_date' => 'required|date',
                'user_id' => 'required|int|min:1|exists:users,id',
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
