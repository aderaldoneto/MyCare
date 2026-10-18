<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $patients = Patient::query()
            ->select(['id', 'name', 'cpf', 'phone', 'birth_date'])
            ->when(trim($validated['search'] ?? '') !== '', function ($query) use ($validated) {
                $search = trim($validated['search']);

                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('cpf', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(10);

        return response()->json([
            'data' => $patients->getCollection()->map(fn (Patient $patient) => [
                'id' => $patient->id,
                'name' => $patient->name,
                'cpf' => $patient->cpf,
                'phone' => $patient->phone,
                'birth_date' => $patient->birth_date?->toDateString(),
            ]),
            'current_page' => $patients->currentPage(),
            'last_page' => $patients->lastPage(),
            'total' => $patients->total(),
        ]);
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'count' => Patient::query()->count(),
        ]);
    }
}
