<?php

namespace App\Http\Middleware;

use App\Models\ArcadeMachine;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMachineIsValid
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Token da maquina ausente ou invalido.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $machine = ArcadeMachine::where('api_token', $token)->first();

        if (!$machine) {
            return response()->json([
                'status' => 'ERROR',
                'message' => 'Maquina nao autorizada ou credenciais incorretas.'
            ], Response::HTTP_FORBIDDEN);
        }

        $request->attributes->set('authenticated_machine', $machine);

        return $next($request);
    }
}
