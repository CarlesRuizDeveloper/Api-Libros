<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Password;

class UserController extends Controller
{
    public function crearUsuario(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:Usuario,Administrador,PURU,SwaggerTester',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $user->assignRole($request->role);

        return response()->json(['user' => $user], 201);
    }

    public function editarUsuario(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6',
            'role' => 'in:Usuario,Administrador,PURU,SwaggerTester',
            'status' => 'in:active,deleted',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only('email', 'password', 'role', 'status'));

        if ($request->has('role')) {
            $user->syncRoles($request->role);
        }

        return response()->json(['user' => $user], 200);
    }

    public function borrarUsuario($id)
    {
        $user = User::findOrFail($id);
        $user->update(['status' => 'deleted']);

        return response()->json(['message' => 'User deleted successfully'], 200);
    }

    public function recuperarPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        Password::sendResetLink($request->only('email'));

        return response()->json(['message' => 'Password reset link sent'], 200);
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $response = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            return response()->json(['message' => 'Password reset successfully'], 200);
        } else {
            return response()->json(['message' => 'Failed to reset password'], 400);
        }
    }
}

