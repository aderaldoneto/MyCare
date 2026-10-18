<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', 'unique:patients,cpf'],
            'rg' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'sex' => ['nullable', 'in:m,f'],
            'phone' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255', 'unique:patients,email'],
            'description' => ['nullable', 'string'],
        ], [
            'cpf.unique' => 'Já existe um paciente cadastrado com este CPF.',
            'email.unique' => 'Já existe um paciente cadastrado com este e-mail.',
        ]);

        $patient = Patient::create($validated);

        return response()->json($patient, 201);
    }

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
