<?php

# this will be my user controller

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::all();
            return response()->json($users);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Failed to retrieve users', 'error' => $e->getMessage()], 500);
        }
    }
    public function show($id)
    {
        try {
            $user = User::findOrFail($id);
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve user', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            // flag user as deleted
            $user->is_deleted = true;
            $user->save();
            // delete unsold items of this user
            DB::transaction(function () use ($user) {
                $user->unsoldItems()->delete();
            }, 5);
            return response()->json($user);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete user', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'name' => 'required|string|min:4|max:255',
                'username' => 'required|string|min:4|max:255|unique:users',
                'email' => 'required|email|unique:users',
                'profile_description' => 'nullable|string|min:4|max:255',
                'city' => 'nullable|string|min:4|max:255',
                'state' => 'nullable|string|min:4|max:255',
                'country' => 'nullable|string|min:4|max:255',
                'address' => 'nullable|string|min:4|max:255',
                'zip_code' => 'nullable|int|min:0|max:99999',
            ]);

            $user = User::create($validatedData);

            return response()->json(['message' => 'User created', 'user' => $user], 201);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create user', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            if (!is_numeric($id)) {
                return response()->json(['error' => 'Invalid user id'], 400);
            }

            $user = User::findOrfail($id);

            $validatedData = $request->validate([
                'name' => 'required|string|min:4|max:255',
                'username' => 'required|string|min:4|max:255|unique:users,username,' . $id,
                'email' => 'required|email|unique:users,email,' . $id,
                'profile_description' => 'nullable|string|min:4|max:255',
                'city' => 'nullable|string|min:4|max:255',
                'state' => 'nullable|string|min:4|max:255',
                'country' => 'nullable|string|min:4|max:255',
                'address' => 'nullable|string|min:4|max:255',
                'zip_code' => 'nullable|int|min:0|max:99999',
            ]);

            $user->update($validatedData);

            return response()->json($user, 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'User not found'], 404);
        } catch (ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user', 'error' => $e->getMessage()], 500);
        }
    }
}
