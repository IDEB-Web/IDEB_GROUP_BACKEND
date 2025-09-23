<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // Listar usuarios pendientes
    public function getPendingUsers()
    {
        $users = User::where('status', 'pendiente')->get();
        return response()->json($users);
    }

    // Aprobar usuario
 public function approve($id)
{
    $user = User::findOrFail($id);
    $user->status = 'aprobado';
    $user->save();

    return response()->json([
        'message' => 'Usuario aprobado exitosamente.',
        'user' => $user
    ]);
}

public function reject($id)
{
    $user = User::findOrFail($id);
    $user->status = 'rechazado';
    $user->save();

    return response()->json([
        'message' => 'Usuario rechazado exitosamente.',
        'user' => $user
    ]);
}

}