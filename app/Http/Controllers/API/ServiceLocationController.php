namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ServiceLocation;
use Illuminate\Support\Facades\Validator;

class ServiceLocationController extends Controller
{
    /**
     * Display a listing of serviceable pincodes.
     */
    public function index()
    {
        $locations = ServiceLocation::where('is_active', true)
            ->select('pincode', 'area_name', 'district', 'state')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $locations
        ]);
    }

    /**
     * Store a new serviceable pincode.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'pincode' => 'required|string|size:6|unique:service_locations',
            'area_name' => 'required|string|max:255',
            'district' => 'required|string|max:255',
            'state' => 'required|string|max:255',
            'country' => 'sometimes|string|max:255'
        ]);

        $location = ServiceLocation::create($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Service location added successfully',
            'data' => $location
        ], 201);
    }

    /**
     * Check if delivery is available for a pincode.
     */
    public function checkServiceability(Request $request)
    {
        $validated = $request->validate([
            'pincode' => 'required|string|size:6'
        ]);

        $location = ServiceLocation::where('pincode', $validated['pincode'])
            ->where('is_active', true)
            ->first();

        if (!$location) {
            return response()->json([
                'status' => 'error',
                'message' => 'Delivery is not available at this pincode'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Delivery is available',
            'data' => [
                'area_name' => $location->area_name,
                'district' => $location->district,
                'state' => $location->state
            ]
        ]);
    }

    /**
     * Update service location settings.
     */
    public function update(Request $request, ServiceLocation $serviceLocation)
    {
        $validated = $request->validate([
            'area_name' => 'sometimes|string|max:255',
            'district' => 'sometimes|string|max:255',
            'state' => 'sometimes|string|max:255',
            'country' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean'
        ]);

        $serviceLocation->update($validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Service location updated successfully',
            'data' => $serviceLocation
        ]);
    }

    /**
     * Remove a serviceable pincode.
     */
    public function destroy(ServiceLocation $serviceLocation)
    {
        $serviceLocation->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Service location removed successfully'
        ]);
    }
}
