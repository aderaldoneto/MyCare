<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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

    public function show(Patient $patient): JsonResponse
    {
        return response()->json($this->patientData($patient));
    }

    public function update(Request $request, Patient $patient): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'cpf' => ['required', 'string', 'max:14', Rule::unique('patients', 'cpf')->ignore($patient->id)],
            'rg' => ['required', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before_or_equal:today'],
            'sex' => ['required', 'in:m,f'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('patients', 'email')->ignore($patient->id),
            ],
            'description' => ['nullable', 'string'],
        ], [
            'cpf.unique' => 'Já existe um paciente cadastrado com este CPF.',
            'email.unique' => 'Já existe um paciente cadastrado com este e-mail.',
        ]);

        $patient->update($validated);

        return response()->json($this->patientData($patient->refresh()));
    }

    public function destroy(Patient $patient): JsonResponse
    {
        $patient->delete();

        return response()->json(['message' => 'Paciente excluído com sucesso.']);
    }

    public function count(): JsonResponse
    {
        return response()->json([
            'count' => Patient::query()->count(),
        ]);
    }

    private function patientData(Patient $patient): array
    {
        return [
            'id' => $patient->id,
            'name' => $patient->name,
            'cpf' => $patient->cpf,
            'rg' => $patient->rg,
            'birth_date' => $patient->birth_date?->toDateString(),
            'sex' => $patient->sex,
            'phone' => $patient->phone,
            'email' => $patient->email,
            'description' => $patient->description,
        ];
    }
}
