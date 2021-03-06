<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\PasswordResetRequest;
use App\Notifications\PasswordResetSuccess;
use App\Notifications\SignupActivate;
use Carbon\Carbon;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Http\Resources\User as UserResource;
use App\Http\Resources\UserCollection;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;


class UserController extends Controller
{
    private static $messages = [
        'required'=>'El campo: atribute es obligatorio',
    ];
    public function authenticate(Request $request)
    {
        $credentials = $request->only('email', 'password');
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 400);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        $user = JWTAuth::user();
        return response()->json(compact('user','token'))
            ->withCookie(
                'token',
                $token,
                config('jwt.ttl'), // ttl => time to live
                '/', // path
                null, // domain
                config('app.env') !== 'local', // Secure
                true, // httpOnly
                false, //
                config('app.env') !== 'local' ? 'None' : 'Lax' // SameSite
            );
    }
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone'=> 'required',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'direction' => 'required|string|max:255',
            'role'=>'required|string|max:255',
            'description'=>'required|string|max:255'
        ]);
        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }
        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random=str_shuffle($permitted_chars);

        $user = User::create([
            'name' => $request->get('name'),
            'last_name'=>$request->get('last_name'),
            'phone'=>$request->get('phone'),
            'email' => $request->get('email'),
            'password' => Hash::make($request->get('password')),
            'direction'=>$request->get('direction'),
            'activation_code'  => $random,
            'role'=>$request->get('role'),
            'description'=>$request->get('description')
        ]);
        $user->notify(new SignupActivate($user));
        $token = JWTAuth::fromUser($user);

        return response()->json(new UserResource($user, $token), 201)
            ->withCookie(
                'token',
                $token,
                config('jwt.ttl'),
                '/',
                null,
                config('app.env') !== 'local',
                true,
                false,
                config('app.env') !== 'local' ? 'None' : 'Lax'
            );
    }
    public function getAuthenticatedUser()
    {
        try {
            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['message' => 'user_not_found'], 404);
            }
        } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'token_expired'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {
            return response()->json(['message' => 'token_invalid'], $e->getStatusCode());
        } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {
            return response()->json(['message' => 'token_absent'], $e->getStatusCode());
        }
        return response()->json(new UserResource($user), 200);
    }
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                "status" => "success",
                "message" => "User successfully logged out."
            ], 200);
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(["message" => "No se pudo cerrar la sesi??n."], 500);
        }
    }
    // aqui la verificacion de correo
    public function verify($code)
    {
        $active = User::where('active', true)->first();
        $user = User::where('activation_code', $code)->first();

        if (!$user) {
            return response()->json(['message' => 'El token de activaci??n es inv??lido'], 404);
        }

        // After verifying remove token (unnecessary)
        $user->activation_code = '';
        $user->active=true;
        $user->save();

        return $user;
    }

    public function resend(User $user)
    {
        $active = User::where('active', true)->first();
        $user = User::where('activation_code', $code)->first();

        if(!$user)
        {
            return response()->json(["message" => 'La cuenta ha sido verificada'], 409);
        }

        $user->notify(new SignupActivate($user));
        return response()->json(["message" => 'Se ha reenviado el correo electr??nico de validaci??n'], 200);
    }


    public function create(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
        ]);
        $user = \App\User::where('email', $request->email)->first();
        if (!$user)
            return response()->json([
                'message' => 'We can\'t find a user with that e-mail address.'
            ], 404);

        $permitted_chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $random=str_shuffle($permitted_chars);

        $passwordReset = \App\PasswordReset::updateOrCreate(
            ['email' => $user->email],
            [
                'email' => $user->email,
                'token' => $random,
            ]
        );
        if ($user && $passwordReset)
            $user->notify(
                new PasswordResetRequest($passwordReset->token)
            );
        return response()->json([
            'message' => 'We have e-mailed your password reset link!'
        ]);
    }

    public function find($token)
    {
        $passwordReset = PasswordReset::where('token', $token)->first();
        if (!$passwordReset)
        {
            return response()->json(['message' => 'This password reset token is invalid.'], 404);
        }

        if (Carbon::parse($passwordReset->updated_at)->addMinutes(720)->isPast()) {
            $passwordReset->delete();
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        }
        return response()->json($passwordReset);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string|confirmed',
            'token' => 'required|string'
        ]);
        $passwordReset = PasswordReset::where('token', $request->token)->first();
        if (!$passwordReset)
            return response()->json([
                'message' => 'This password reset token is invalid.'
            ], 404);
        $user = User::where('email', $request->email)->first();
        if (!$user)
            return response()->json([
                'message' => 'We can\'t find a user with that e-mail address.'
            ], 404);
        $user->password = Hash::make($request->password);
        $user->save();
        $passwordReset->delete();
        $user->notify(new PasswordResetSuccess($passwordReset));
        return response()->json($user);
    }


public function index()
    {
        return new UserCollection(User::paginate());
    }
    public function show(User $user)
    {
        return response()->json(new UserCollection($user),200);
    }
    public function store(Request $request){
        $user = User::create($request->all());
        return response()->json($user,201);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'phone' => 'required|string',
            'email' => 'required|string',
            'description'=> 'required|string',
        ]);
        $user->update($request->all());
        return response()->json($user,200);
    }
    public function delete(User $user)
    {
        $user->delete();
        return response()->json(null,204);
    }
}


