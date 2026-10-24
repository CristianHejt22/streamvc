<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Captura amigable de errores de base de datos para Vercel Serverless
        $this->renderable(function (\PDOException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'database_error',
                    'message' => 'No se pudo conectar a la base de datos MySQL en Vercel.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return response()->view('errors.db_setup', [
                'error' => $e->getMessage(),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'driver' => config('database.default'),
            ], 500);
        });

        $this->renderable(function (\Illuminate\Database\QueryException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'status' => 'database_error',
                    'message' => 'Error de consulta o tablas no migradas en la base de datos.',
                    'error' => $e->getMessage(),
                ], 500);
            }

            return response()->view('errors.db_setup', [
                'error' => $e->getMessage(),
                'host' => config('database.connections.' . config('database.default') . '.host'),
                'database' => config('database.connections.' . config('database.default') . '.database'),
                'driver' => config('database.default'),
            ], 500);
        });
    }
}
