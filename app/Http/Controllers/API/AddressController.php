<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Address;
use Illuminate\Support\Facades\Validator;

class AddressController extends Controller
{
    public function index()
    {
        $addresses = auth()->user()->addresses;
        return response()->json([
            'status' => true,
            'addresses' => $addresses
        ]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'pincode' => 'required|string',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = auth()->user();
        
        // If this is the first address or is_default is true, update other addresses
        if ($request->is_default || $user->addresses()->count() === 0) {
            $user->addresses()->update(['is_default' => false]);
            $request->merge(['is_default' => true]);
        }

        $address = $user->addresses()->create($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Address added successfully',
            'address' => $address
        ], 201);
    }

    public function show(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        return response()->json([
            'status' => true,
            'address' => $address
        ]);
    }

    public function update(Request $request, Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address_line1' => 'required|string',
            'address_line2' => 'nullable|string',
            'city' => 'required|string',
            'state' => 'required|string',
            'country' => 'required|string',
            'pincode' => 'required|string',
            'is_default' => 'boolean'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If setting as default, update other addresses
        if ($request->is_default) {
            auth()->user()->addresses()->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json([
            'status' => true,
            'message' => 'Address updated successfully',
            'address' => $address
        ]);
    }

    public function destroy(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        // If deleting default address, make the next available address as default
        if ($address->is_default) {
            $nextAddress = auth()->user()->addresses()
                ->where('id', '!=', $address->id)
                ->first();
            
            if ($nextAddress) {
                $nextAddress->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json([
            'status' => true,
            'message' => 'Address deleted successfully'
        ]);
    }

    public function setDefault(Address $address)
    {
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);

        return response()->json([
            'status' => true,
            'message' => 'Default address updated successfully',
            'address' => $address
        ]);
    }
}
