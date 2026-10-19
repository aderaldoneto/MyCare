<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\User;
use App\Support\RedisCache;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'page' => ['nullable', 'integer', 'min:1'],
        ]);

        $search = trim($validated['search'] ?? '');
        $page = $validated['page'] ?? 1;
        $cacheKey = 'list:'.sha1(json_encode([$search, $page]));

        $response = app(RedisCache::class)->remember(
            'users',
            $cacheKey,
            function () use ($search, $page): array {
                $users = User::query()
                    ->select(['id', 'name', 'email', 'role'])
                    ->when($search !== '', function ($query) use ($search) {
                        $query->where(function ($query) use ($search) {
                            $query->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                    })
                    ->orderBy('name')
                    ->paginate(10, ['*'], 'page', $page);

                return [
                    'data' => $users->getCollection()->map(fn (User $user) => $this->userData($user))->all(),
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'total' => $users->total(),
                ];
            }
        );

        return response()->json($response);
    }

    public function count(): JsonResponse
    {
        $count = app(RedisCache::class)->remember(
            'users',
            'count',
            fn (): int => User::query()->count()
        );

        return response()->json(['count' => $count]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ], [
            'email.unique' => 'Já existe um usuário cadastrado com este e-mail.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        ]);

        $user = User::create($validated);
        app(RedisCache::class)->flush('users');

        return response()->json($this->userData($user), 201);
    }

    public function show(User $user): JsonResponse
    {
        return response()->json($this->userData($user));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:5', 'max:255'],
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'role' => ['required', Rule::enum(Role::class)],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ], [
            'email.unique' => 'Já existe um usuário cadastrado com este e-mail.',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres.',
        ]);

        if (
            $user->role === Role::Admin &&
            $validated['role'] !== Role::Admin->value &&
            User::query()->where('role', Role::Admin->value)->count() <= 1
        ) {
            return response()->json([
                'message' => 'Não é possível remover o perfil do último administrador.',
            ], 422);
        }

        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);
        app(RedisCache::class)->flush('users');

        return response()->json($this->userData($user->refresh()));
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()->is($user)) {
            return response()->json([
                'message' => 'Não é possível excluir o usuário conectado.',
            ], 422);
        }

        if (
            $user->role === Role::Admin &&
            User::query()->where('role', Role::Admin->value)->count() <= 1
        ) {
            return response()->json([
                'message' => 'Não é possível excluir o último administrador.',
            ], 422);
        }

        $user->delete();
        app(RedisCache::class)->flush('users');

        return response()->json(['message' => 'Usuário excluído com sucesso.']);
    }

    private function userData(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
        ];
    }
}
