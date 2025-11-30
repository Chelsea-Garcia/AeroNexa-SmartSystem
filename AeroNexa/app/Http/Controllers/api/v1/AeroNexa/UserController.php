<?php


namespace App\Http\Controllers\api\v1\aeronexa;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\aeronexa\User;
use Illuminate\Support\Facades\Hash;


class UserController extends Controller
{
    // List users (paginated)
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 25);
        $users = User::paginate($perPage);
        return response()->json($users);
    }


    // Show single user
    public function show($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        return response()->json($user);
    }
    // Create user (simple validation, no tokens)
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'email' => 'required|email|unique:aeronexa_users,email',
            'phone' => 'nullable|string|max:50',
            'password' => 'required|string|min:6',
        ]);


        $data['password'] = Hash::make($data['password']);


        $user = User::create($data);


        return response()->json($user, 201);
    }


    // Update user
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }


        $data = $request->validate([
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|nullable|string|max:255',
            'email' => 'sometimes|required|email|unique:aeronexa_users,email,' . $id,
            'phone' => 'sometimes|nullable|string|max:50',
            'password' => 'sometimes|nullable|string|min:6',
        ]);


        if (! empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }


        $user->update($data);


        return response()->json($user);
    }


    // Delete user
    public function destroy($id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        $user->delete();
        return response()->json(['message' => 'User deleted']);
    }


    // Simple "login" endpoint (no tokens) — returns user if email/password match
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);


        $user = User::where('email', $data['email'])->first();
        if (! $user || ! Hash::check($data['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }


        // No token — return user object only
        return response()->json($user);
    }
}
