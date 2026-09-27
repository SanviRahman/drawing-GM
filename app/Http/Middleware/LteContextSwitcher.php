<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Symfony\Component\HttpFoundation\Response;

class LteContextSwitcher
{
    public function handle(Request $request,Closure $next,string $guard): Response {
        Auth::shouldUse($guard);

        $configMap = [
            'admin' => 'adminlte_admin',
        ];

        $configFileName = $configMap[$guard] ?? null;
        if (
            $configFileName !== null
            && Config::has($configFileName)
        ) {
            Config::set(
                'adminlte',
                Config::get($configFileName)
            );
        }

        return $next($request);
    }
}