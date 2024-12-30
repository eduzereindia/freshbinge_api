<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Class AddressController
 * 
 * Handles all address-related operations including creating, updating,
 * and managing default addresses for users.
 */
class AddressController extends Controller
{
    /**
     * Get all addresses for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $addresses = $request->user()->addresses()->with('serviceLocation')->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'addresses' => $addresses
            ]
        ]);
    }

    /**
     * Store a new address for the authenticated user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|size:10',
            'address_line1' => 'required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'pincode' => 'required|string|size:6',
            'address_type' => 'required|in:home,office,other',
            'is_default' => 'boolean',
            'service_location_id' => 'required|exists:service_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If this is the first address or is_default is true, unset other default addresses
        if ($request->is_default || !$request->user()->addresses()->exists()) {
            $request->user()->addresses()->update(['is_default' => false]);
            $request->merge(['is_default' => true]);
        }

        $address = $request->user()->addresses()->create($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Address added successfully',
            'data' => [
                'address' => $address->load('serviceLocation')
            ]
        ], 201);
    }

    /**
     * Get a specific address.
     *
     * @param Address $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Address $address)
    {
        // Check if address belongs to authenticated user
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'address' => $address->load('serviceLocation')
            ]
        ]);
    }

    /**
     * Update a specific address.
     *
     * @param Request $request
     * @param Address $address
     * @return \Illuminate\Http\JsonResponse
     * 
     * @throws ValidationException
     */
    public function update(Request $request, Address $address)
    {
        // Check if address belongs to authenticated user
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'mobile' => 'sometimes|required|string|size:10',
            'address_line1' => 'sometimes|required|string|max:255',
            'address_line2' => 'nullable|string|max:255',
            'landmark' => 'nullable|string|max:255',
            'city' => 'sometimes|required|string|max:100',
            'state' => 'sometimes|required|string|max:100',
            'pincode' => 'sometimes|required|string|size:6',
            'address_type' => 'sometimes|required|in:home,office,other',
            'is_default' => 'boolean',
            'service_location_id' => 'sometimes|required|exists:service_locations,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation Error',
                'errors' => $validator->errors()
            ], 422);
        }

        // If setting as default, unset other default addresses
        if ($request->has('is_default') && $request->is_default) {
            $request->user()->addresses()->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }

        $address->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Address updated successfully',
            'data' => [
                'address' => $address->fresh()->load('serviceLocation')
            ]
        ]);
    }

    /**
     * Delete a specific address.
     *
     * @param Address $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Address $address)
    {
        // Check if address belongs to authenticated user
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // If deleting default address, make the oldest remaining address default
        if ($address->is_default) {
            $newDefault = auth()->user()->addresses()
                ->where('id', '!=', $address->id)
                ->oldest()
                ->first();
            
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        $address->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Address deleted successfully'
        ]);
    }

    /**
     * Set an address as default.
     *
     * @param Address $address
     * @return \Illuminate\Http\JsonResponse
     */
    public function setDefault(Address $address)
    {
        // Check if address belongs to authenticated user
        if ($address->user_id !== auth()->id()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unauthorized access'
            ], 403);
        }

        // Unset other default addresses
        auth()->user()->addresses()->where('id', '!=', $address->id)
            ->update(['is_default' => false]);

        $address->update(['is_default' => true]);

        return response()->json([
            'status' => 'success',
            'message' => 'Address set as default successfully',
            'data' => [
                'address' => $address->fresh()->load('serviceLocation')
            ]
        ]);
    }
}
