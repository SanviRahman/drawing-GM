<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class ApplyRedirects
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        if ($request->is('admin') || $request->is('admin/*')) {
            return $next($request);
        }

        if (! Schema::hasTable('redirects')) {
            return $next($request);
        }

        $path = '/' . ltrim($request->path(), '/');
        $path = $path !== '/' ? rtrim($path, '/') : '/';

        $redirect = Redirect::query()
            ->active()
            ->where('from_path', $path)
            ->first();

        if (! $redirect) {
            return $next($request);
        }

        if ($this->wouldLoop($request, $redirect->to_url)) {
            return $next($request);
        }

        Redirect::query()
            ->whereKey($redirect->id)
            ->update([
                'hits' => $redirect->hits + 1,
                'last_hit_at' => now(),
            ]);

        return redirect()->to($redirect->to_url, $redirect->status_code);
    }

    private function wouldLoop(Request $request, string $toUrl): bool
    {
        $current = rtrim($request->fullUrl(), '/');
        $absoluteTarget = str_starts_with($toUrl, '/')
            ? rtrim($request->getSchemeAndHttpHost() . $toUrl, '/')
            : rtrim($toUrl, '/');

        return $absoluteTarget === $current;
    }
}