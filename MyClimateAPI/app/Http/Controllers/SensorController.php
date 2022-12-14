<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateSensorRequest;
use App\Http\Resources\SensorResource;
use App\Services\HomeService;
use App\Services\SensorService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class SensorController extends Controller
{
    protected $homeService;
    protected $sensorService;

    public function __construct(HomeService $homeService, SensorService $sensorService){
        $this->homeService = $homeService;
        $this->sensorService = $sensorService;
    }

    /**
     * Create a sensor
     * @url POST /homes/{id}/sensors
     * @param CreateSensorRequest $request
     * @param int $id
     * @return JsonResponse
     */
    public function store(CreateSensorRequest $request, int $id){
        // User is authenticated --> Checked by sanctum middleware
        $user = Auth::user();

        $home = $this->homeService->findByIdOrFail($id); // If not found -> 404

        // Return 403 if the user is not the owner of the house
        abort_if($home->user_id !== $user->id, 403);

        $validatedData = $request->validated();

        // Append home id
        $validatedData['home_id'] = $home->id;

        $sensor = $this->sensorService->createSensor($validatedData);

        return response()->json(['data' => new SensorResource($sensor)], 201);

    }


    /**
     * Get all sensors of a house
     * @url GET /homes/{id}/sensors
     * @param Request $request
     * @param int $id
     * @return AnonymousResourceCollection
     */
    public function index(Request $request, int $id): AnonymousResourceCollection
    {
        // User is authenticated --> Checked by sanctum middleware
        $user = Auth::user();

        $home = $this->homeService->findByIdOrFail($id); // If not found -> 404

        // Return 403 if the user is not the owner of the house
        abort_if($home->user_id !== $user->id, 403);


        // Pagination stuff
        $page = $request->get('page') !== null ? $request->get('page') : 1;
        $per_page = $request->get('perPage') !== null ? $request->get('perPage') : 10;

        return SensorResource::collection($home->sensors()->paginate($per_page, ['*'], 'page', $page));

    }


    /**
     * Delete a sensor
     * @url DELETE /sensors/{id}
     * @param int $id
     * @return Response
     */
    public function destroy(int $id): Response
    {
        // User is authenticated --> Checked by sanctum middleware
        $user = Auth::user();

        $sensor = $this->sensorService->findByIdOrFail($id); // If not found -> 404

        // Return 403 if the user is not the owner of the sensor
        abort_if($sensor->home->user_id !== $user->id, 403);

        // Delete the sensor
        $this->sensorService->deleteSensor($sensor);

        // Return 204
        return response()->noContent();
    }
}
