<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientController extends Controller
{
    public function count(): JsonResponse
    {
        return response()->json([
            'count' => Patient::query()->count(),
        ]);
    }
}
