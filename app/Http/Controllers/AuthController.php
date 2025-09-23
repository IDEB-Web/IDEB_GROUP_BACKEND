<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Http;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|email|unique:users',
            'password'=>'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'=>$request->name,
            'email'=>$request->email,
            'password'=>Hash::make($request->password),
            'role'=>'user',
            'status'=>'pendiente',
        ]);

        return response()->json(['message'=>'Registro exitoso. Cuenta pendiente de aprobación.'],201);
    }

    public function login(Request $request)
    {
        $request->validate(['email'=>'required|email','password'=>'required']);

        $user = User::where('email',$request->email)->first();
        if(!$user || !Hash::check($request->password,$user->password)) {
            throw ValidationException::withMessages(['email'=>['Credenciales incorrectas.']]);
        }
        if($user->status !== 'aprobado') {
            return response()->json(['message'=>'Cuenta pendiente o rechazada.'],403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message'=>'Login exitoso',
            'access_token'=>$token,
            'token_type'=>'Bearer',
            'user'=>$user
        ]);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function handleGoogleCallback()
{
    try {
        $googleUser = Socialite::driver('google')->stateless()->user();
        $user = User::where('email', $googleUser->getEmail())->first();

        $frontend = config('app.frontend_url', env('FRONTEND_URL', 'http://localhost:4200'));

        if(!$user) {
            $user = User::create([
                'name' => $googleUser->getName(),
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'password' => null,
                'status' => 'pendiente',
            ]);

            return redirect()->to($frontend . '/auth/pending');
        }

        if($user->status !== 'aprobado') {
            return redirect()->to($frontend . '/auth/pending');
        }

        if(!$user->google_id){
            $user->google_id = $googleUser->getId();
            $user->save();
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return redirect()->to($frontend . '/auth/callback?token=' . $token);

    } catch (\Exception $e) {
        return redirect()->to($frontend . '/auth/error?message=' . urlencode($e->getMessage()));
    }
}

    public function googleSignIn(Request $request)
    {
        $request->validate(['token' => 'required|string']);

        $idToken = $request->input('token');

        try {
            $resp = Http::get('https://oauth2.googleapis.com/tokeninfo', [
                'id_token' => $idToken
            ]);

            if ($resp->failed()) {
                return response()->json(['message' => 'Token inválido'], 401);
            }

            $payload = $resp->json();

            $clientId = env('GOOGLE_CLIENT_ID');
            if ($clientId && isset($payload['aud']) && $payload['aud'] !== $clientId) {
                return response()->json(['message' => 'Token inválido (aud mismatch)'], 401);
            }

            $email = $payload['email'] ?? null;
            $name  = $payload['name'] ?? ($payload['email'] ?? 'Usuario');

            if (!$email) {
                return response()->json(['message' => 'Email no disponible en token'], 400);
            }

            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => $email,
                    'google_id' => $payload['sub'] ?? null,
                    'password' => null,
                    'status' => 'pendiente',
                ]);

                return response()->json([
                    'message' => 'Cuenta creada, pendiente de aprobación.',
                    'status' => $user->status
                ], 201);
            }

            if ($user->status !== 'aprobado') {
                return response()->json(['message' => 'Cuenta pendiente o rechazada.'],403);
            }

            if (!$user->google_id) {
                $user->google_id = $payload['sub'] ?? null;
                $user->save();
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login exitoso',
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error en verificación de token: ' . $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Sesión cerrada.']);
    }
}


