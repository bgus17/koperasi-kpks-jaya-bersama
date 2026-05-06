<?php
// ============================================================
// FILE: app/Http/Middleware/EnsureJsonResponse.php
// Memastikan semua response API selalu dalam format JSON
// ============================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureJsonResponse
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->headers->set('Accept', 'application/json');

        $response = $next($request);

        if (!$response->headers->has('Content-Type')) {
            $response->headers->set('Content-Type', 'application/json');
        }

        return $response;
    }
}


// ============================================================
// FILE: app/Http/Middleware/SanitizeInput.php
// Membersihkan input dari spasi berlebih & karakter berbahaya
// ============================================================

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeInput
{
    /**
     * Kolom yang TIDAK perlu di-trim (misal: password)
     */
    protected array $except = ['password', 'password_confirmation'];

    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->except($this->except);
        $input = $this->clean($input);
        $request->merge($input);

        return $next($request);
    }

    private function clean(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->clean($value);
            } elseif (is_string($value)) {
                $data[$key] = trim(strip_tags($value));
            }
        }

        return $data;
    }
}