<?php

# this will be my user controller

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use App\Models\Item;
use App\Models\User;
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
                    } elseif ($column === 'title') {
                        $query->where('title', 'like', "%$value%");
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
            // check first if the item is sold or is related to a transaction
            if ($item->state === 'sold' || $item->transaction()->exists()) {
                return response()->json(['error' => 'Cannot delete sold items'], 400);
            }
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
            // Validate the request
            $validatedData = $request->validate([
                'title' => 'required|string|min:4|max:255',
                'description' => 'required|string|min:4|max:255',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255', // 'Cascos', 'Monos', 'Guantes', 'Chaquetas', 'Pantalones', 'Botas', 'Accesorios', 'Ropa Interior', 'Recambios'
                'location' => 'nullable|string|max:255',
                'state' => 'nullable|in:available,reserved',
                'condition' => 'required|int|min:0|max:3',
                'publishDate' => 'required|date',
                'userId' => 'required|int|min:1|exists:users,id',
                'images' => 'nullable|array|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // Set location to the owner's location if not provided
            if (!isset($validatedData['location'])) {
                $owner = User::findOrFail($validatedData['userId']);
                $validatedData['location'] = "{$owner->city}, {$owner->state}";
            }
            // remove the images key-value from validatedData
            $creationData = array_filter($validatedData, function ($key) {
                return $key !== 'images';
            }, ARRAY_FILTER_USE_KEY);

            // Create the item
            $item = Item::create($creationData);

            // Upload images to Cloudinary with the item's ID as part of the folder structure
            $uploadedImageUrls = [];
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $image) {
                    // Upload image to Cloudinary
                    try {
                        $uploadedFile = Cloudinary::upload($image->getRealPath(), [
                            'folder' => 'reborn/' . $item->id, // Folder structure: reborn/{itemId}
                        ]);

                        // Put into the array the secure URL of the uploaded image
                        array_push($uploadedImageUrls, $uploadedFile->getSecurePath());
                    } catch (\Exception $e) {
                        // Delete the item if image upload fails
                        $item->delete();
                        throw new \Exception('Failed to upload image: ' . $e->getMessage());
                    }
                }
            }

            // Update the item with the image URLs
            $item->update(['images' => $uploadedImageUrls]);

            return response()->json(['message' => 'Item created', 'item' => $item], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            // If any exception occurs, delete the item if it was created
            if (isset($item)) {
                $item->delete();
            }
            return response()->json(['error' => 'Failed to create item: ' . $e->getMessage()], 500);
        }
    }


    public function update(Request $request, $id)
    {
        try {
            // Validate the UUID format
            if (!preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}/', $id)) {
                return response()->json(['error' => 'Invalid item id'], 400);
            }

            // Find the item
            $item = Item::findOrFail($id);

            // Check if the item can be updated
            if (!$item->canBeUpdated()) {
                return response()->json(['error' => 'Sold items cannot be updated'], 400);
            }

            // Validate the request
            $validatedData = $request->validate([
                'title' => 'required|string|min:4|max:255',
                'description' => 'required|string|min:4|max:255',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'state' => 'nullable|in:available,sold,reserved',
                'location' => 'nullable|string|max:255',
                'condition' => 'required|int|min:0|max:3',
                'publishDate' => 'required|date',
                'userId' => 'required|int|min:1|exists:users,id',
                'images' => 'nullable|array|max:5',
                'images.*' => 'image|mimes:jpeg,png,jpg|max:2048',
            ]);

            // if the item has not location setted and is not provided by the request, build it as did in store method
            if (!isset($item->location) || $item->location == '' && !isset($validatedData['location'])) {
                $owner = User::findOrFail($validatedData['userId']);
                $validatedData['location'] = "{$owner->city}, {$owner->state}";
            }

            // Upload new images to Cloudinary if provided
            if ($request->hasFile('images')) {
                $uploadedImageUrls = [];
                foreach ($request->file('images') as $image) {
                    // Upload image to Cloudinary
                    try {
                        $uploadedFile = Cloudinary::upload($image->getRealPath(), [
                            'folder' => 'reborn/' . $item->id, // Folder structure: reborn/{itemId}
                        ]);

                        // Put into the array the secure URL of the uploaded image
                        array_push($uploadedImageUrls, $uploadedFile->getSecurePath());
                    } catch (\Exception $e) {
                        throw new \Exception('Failed to upload image: ' . $e->getMessage());
                    }
                }
                $validatedData['images'] = $uploadedImageUrls;
            }

            // Update the item
            $item->update($validatedData);

            return response()->json(['message' => 'Item updated', 'item' => $item]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Item not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update item: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Map the condition to the database value.
     */
    private function mapCondition($condition)
    {
        switch ($condition) {
            case 'Nuevo':
                return 1; // 1 represents 'Nuevo'
            case 'Como nuevo':
                return 2; // 2 represents 'Como nuevo'
            case 'Buen estado':
                return 3; // 3 represents 'Buen estado'
            default:
                return 0; // 0 represents 'unknown'
        }
    }
}
