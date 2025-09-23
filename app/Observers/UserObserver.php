<?php

namespace App\Observers;
use App\Models\User;
use App\Models\ActivityLog;

class UserObserver
{
    /**
     * Handle the User "updating" event.
     *
     * Este método se ejecuta automáticamente cuando un registro de usuario
     * está a punto de ser actualizado en la base de datos.
     *
     * @param  \App\Models\User  $user
     * @return void
     */
    public function updating(User $user): void
    {
        // Verificamos si el campo que cambió fue 'status' y si el nuevo valor es 'aprobado'.
        if ($user->isDirty('status') && $user->status === 'aprobado') {
            
            // --- INICIO DE LA MODIFICACIÓN ---
            //
            // El error que describes ("falta el campo name") ocurre porque, muy probablemente,
            // aquí se intenta crear un registro en otra tabla (ej. una tabla de auditoría)
            // y se omite el nombre del usuario, que es un campo requerido en esa otra tabla.
            //
            // El objeto '$user' que recibe este método ya tiene todos los datos del usuario,
            // incluyendo el nombre. La solución es simplemente usarlo.
            //
            // A continuación un ejemplo de cómo debería quedar (suponiendo una tabla de logs).
            // Debes adaptar este código a tu implementación real.

            \App\Models\ActivityLog::create([
                'description' => 'El usuario ' . $user->name . ' fue aprobado.',
                'user_id' => $user->id,
                'name' => $user->name, // <-- CORRECCIÓN: El campo en la BD se llama 'name', no 'user_name'.
            ]);
        }
    }
}