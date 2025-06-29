<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Feature;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Validator; 


class FeatureController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Feature::query();

        if ($request->has('title')) {
            $query->where('title', 'like', '%' . $request->title . '%');
        }

        $perPage = $request->input('per_page', 5);
        $features = $query->paginate($perPage);
        return $this->sendResponse('Feature list retrieved successfully.', $features);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $feature = Feature::create($request->all());

        return $this->sendResponse('Feature created successfully.', $feature, 201);
        
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $feature = Feature::find($id);
        if (!$feature) {
            return $this->sendError('Feature not found.', [], 404);
        }
        return $this->sendResponse('Feature retrieved successfully.', $feature);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $feature = Feature::find($id);
        if (!$feature) {
            return $this->sendError('Feature not found.', [], 404);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error', $validator->errors());
        }

        $feature->update($request->all());
        return $this->sendResponse('Feature updated successfully.', $feature);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $feature = Feature::find($id);
        if (!$feature) {
            return $this->sendError('Feature not found.', [], 404);
        }

        $feature->delete();
        return $this->sendResponse('Feature deleted successfully.', null, 204);
    }
}
