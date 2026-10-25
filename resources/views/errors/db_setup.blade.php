<!DOCTYPE html>
<html lang="es" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Base de Datos | CStreame Vercel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-[#0b0f19] text-gray-100 min-h-screen flex items-center justify-center p-4 font-sans">
    <div class="max-w-2xl w-full bg-[#111827] border border-gray-800 rounded-3xl p-6 sm:p-8 shadow-2xl space-y-6">
        <!-- Header -->
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center shrink-0">
                <i data-lucide="database" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-white">¡Vercel y Laravel Funcionando!</h1>
                <p class="text-xs text-gray-400">Paso pendiente: Conectar tu Base de Datos MySQL remota</p>
            </div>
        </div>

        <!-- Mensaje Explicativo -->
        <div class="p-4 rounded-2xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-200/90 leading-relaxed space-y-2">
            <p>
                <strong>¡Excelente noticia!</strong> El servidor PHP en Vercel está 100% activo y respondiendo. Sin embargo, Vercel es una plataforma <em>Serverless</em> que no incluye una base de datos local (<code class="bg-black/30 px-1 py-0.5 rounded font-mono">127.0.0.1</code>).
            </p>
            <p>
                Para que el catálogo de streaming, las ventas y los usuarios funcionen, la aplicación necesita conectarse a una base de datos en la nube (como <strong>Supabase</strong> PostgreSQL o MySQL).
            </p>
        </div>

        <!-- Estado Actual de la Conexión -->
        <div class="p-4 rounded-2xl bg-[#0b0f19] border border-gray-800 space-y-2 text-xs font-mono">
            <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider block font-sans">Diagnóstico Actual:</span>
            <div class="grid grid-cols-2 gap-2 text-slate-300">
                <div><span class="text-gray-500">Driver:</span> {{ $driver }}</div>
                <div><span class="text-gray-500">Host:</span> <span class="text-amber-400">{{ $host ?: '127.0.0.1 (Local)' }}</span></div>
                <div><span class="text-gray-500">Base de Datos:</span> {{ $database }}</div>
                <div><span class="text-gray-500">Estado:</span> <span class="text-rose-400 font-bold">Desconectado</span></div>
            </div>
            <div class="pt-2 border-t border-gray-800 text-[11px] text-rose-300 break-all">
                <strong>Error:</strong> {{ $error }}
            </div>
        </div>

        <!-- Guía de 3 Pasos Rápidos -->
        <div class="space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-white flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 text-emerald-400"></i>
                <span>Pasos para Conectar tu Base de Datos:</span>
            </h3>

            <div class="space-y-2 text-xs text-gray-300">
                <div class="p-3 rounded-xl bg-gray-900/60 border border-gray-800 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[11px] flex items-center justify-center shrink-0">1</span>
                    <div>
                        <p class="font-bold text-white">Obtén una Base de Datos MySQL:</p>
                        <p class="text-gray-400 text-[11px]">Puedes usar tu propio hosting cPanel (habilitando tu IP o <code class="text-emerald-300">%</code> en <em>MySQL Remoto</em>) o crear una gratis en <strong>Clever Cloud</strong>, <strong>Aiven</strong>, <strong>Railway</strong> o <strong>Supabase</strong>.</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-gray-900/60 border border-gray-800 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[11px] flex items-center justify-center shrink-0">2</span>
                    <div>
                        <p class="font-bold text-white">Agrega las Variables en Vercel:</p>
                        <p class="text-gray-400 text-[11px]">En Vercel ve a <strong>Settings ➔ Environment Variables</strong> y agrega: <code class="text-sky-300 font-mono">DB_HOST</code>, <code class="text-sky-300 font-mono">DB_DATABASE</code>, <code class="text-sky-300 font-mono">DB_USERNAME</code>, <code class="text-sky-300 font-mono">DB_PASSWORD</code>, <code class="text-sky-300 font-mono">DB_PORT=3306</code>.</p>
                    </div>
                </div>

                <div class="p-3 rounded-xl bg-gray-900/60 border border-gray-800 flex items-start gap-2.5">
                    <span class="w-5 h-5 rounded-full bg-emerald-500/20 text-emerald-400 font-bold text-[11px] flex items-center justify-center shrink-0">3</span>
                    <div>
                        <p class="font-bold text-white">Redespliega y corre las Migraciones:</p>
                        <p class="text-gray-400 text-[11px]">Haz clic en <strong>Redeploy</strong> en Vercel y luego entra a <a href="/migrate-setup?key=cstreame2026" class="text-emerald-400 underline font-mono">/migrate-setup?key=cstreame2026</a> para crear las tablas en 1 solo clic.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div class="pt-4 border-t border-gray-800 flex flex-wrap items-center justify-between gap-3">
            <a href="/health" class="px-4 py-2.5 rounded-xl bg-gray-800 hover:bg-gray-700 text-white font-bold text-xs flex items-center gap-2 transition-all">
                <i data-lucide="activity" class="w-4 h-4 text-sky-400"></i>
                <span>Ver Diagnóstico JSON (/health)</span>
            </a>

            <a href="/migrate-setup?key=cstreame2026" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs flex items-center gap-2 transition-all shadow-lg shadow-emerald-600/30">
                <i data-lucide="play" class="w-4 h-4"></i>
                <span>Probar Migraciones (/migrate-setup)</span>
            </a>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>
