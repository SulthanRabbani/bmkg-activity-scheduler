<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Region;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RegionController extends Controller
{
    /**
     * Display a listing of regions.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $query = Region::query();

        // Filter by level if provided
        if ($request->has('level')) {
            $query->where('level', $request->get('level'));
        }

        // Filter by parent code if provided
        if ($request->has('parent_code')) {
            $query->where('parent_code', $request->get('parent_code'));
        }

        // Search by name if provided
        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->get('search') . '%');
        }

        $regions = $query->with('parent', 'children')
                        ->orderBy('code')
                        ->paginate($request->get('per_page', 15));

        return response()->json($regions);
    }

    /**
     * Get all provinces (level 1).
     *
     * @return JsonResponse
     */
    public function provinces(): JsonResponse
    {
        $provinces = Region::provinces()
                          ->orderBy('name')
                          ->get();

        return response()->json($provinces);
    }

    /**
     * Get regencies/cities by province code.
     *
     * @param string $provinceCode
     * @return JsonResponse
     */
    public function regenciesByProvince(string $provinceCode): JsonResponse
    {
        $regencies = Region::regencies()
                          ->where('parent_code', $provinceCode)
                          ->orderBy('name')
                          ->get();

        return response()->json($regencies);
    }

    /**
     * Get districts by regency code.
     *
     * @param string $regencyCode
     * @return JsonResponse
     */
    public function districtsByRegency(string $regencyCode): JsonResponse
    {
        $districts = Region::districts()
                          ->where('parent_code', $regencyCode)
                          ->orderBy('name')
                          ->get();

        return response()->json($districts);
    }

    /**
     * Get villages by district code.
     *
     * @param string $districtCode
     * @return JsonResponse
     */
    public function villagesByDistrict(string $districtCode): JsonResponse
    {
        $villages = Region::villages()
                         ->where('parent_code', $districtCode)
                         ->orderBy('name')
                         ->get();

        return response()->json($villages);
    }

    /**
     * Display the specified region.
     *
     * @param string $code
     * @return JsonResponse
     */
    public function show(string $code): JsonResponse
    {
        $region = Region::where('code', $code)
                       ->with('parent', 'children')
                       ->firstOrFail();

        return response()->json([
            'region' => $region,
            'full_path' => $region->full_path,
            'level_name' => $region->level_name,
        ]);
    }

    /**
     * Get region hierarchy (breadcrumb).
     *
     * @param string $code
     * @return JsonResponse
     */
    public function hierarchy(string $code): JsonResponse
    {
        $region = Region::where('code', $code)->firstOrFail();

        $hierarchy = [];
        $current = $region;

        while ($current) {
            array_unshift($hierarchy, $current);
            $current = $current->parent;
        }

        return response()->json($hierarchy);
    }
}
