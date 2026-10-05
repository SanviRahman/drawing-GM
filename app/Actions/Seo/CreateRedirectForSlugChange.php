<?php

namespace App\Actions\Seo;

use App\Models\Redirect;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateRedirectForSlugChange
{
    public function execute(string $fromPath, string $toUrl): Redirect
    {
        $fromPath = $this->normalizePath($fromPath);
        $toUrl = trim($toUrl);

        if ($fromPath === $toUrl) {
            throw ValidationException::withMessages([
                'slug' => 'The old and new public URLs resolve to the same path.',
            ]);
        }

        return DB::transaction(function () use ($fromPath, $toUrl): Redirect {
            $redirect = Redirect::withTrashed()
                ->where('from_path', $fromPath)
                ->lockForUpdate()
                ->first();

            if ($redirect) {
                $redirect->fill([
                    'to_url' => $toUrl,
                    'status_code' => 301,
                    'is_active' => true,
                ]);

                if ($redirect->trashed()) {
                    $redirect->restore();
                }

                $redirect->save();

                return $redirect;
            }

            return Redirect::create([
                'from_path' => $fromPath,
                'to_url' => $toUrl,
                'status_code' => 301,
                'is_active' => true,
            ]);
        });
    }

    private function normalizePath(string $path): string
    {
        $path = trim($path);
        $path = '/' . ltrim($path, '/');

        return $path !== '/' ? rtrim($path, '/') : '/';
    }
}
