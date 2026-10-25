<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Throwable;

class DecryptDatabaseCommand extends Command
{
    protected $signature = 'cstreame:decrypt 
                            {file? : Ruta o nombre del archivo .cstbackup}
                            {--key= : Clave maestra de cifrado}
                            {--out= : Carpeta de salida para los archivos descifrados}';

    protected $description = 'Descifra y verifica la integridad de una copia de seguridad protegida .cstbackup';

    public function handle(): int
    {
        $this->info("=========================================================");
        $this->info("     CSTREAME - DESCIFRADOR DE COPIAS DE SEGURIDAD       ");
        $this->info("=========================================================");

        $backupsDir = base_path('backups');
        $fileName = $this->argument('file');

        if (!$fileName) {
            // Buscar los archivos .cstbackup más recientes
            $files = glob($backupsDir . DIRECTORY_SEPARATOR . '*.cstbackup');
            if (empty($files)) {
                $this->error("No se encontraron archivos .cstbackup en {$backupsDir}");
                return 1;
            }
            rsort($files);
            $fileName = basename($files[0]);
            $this->line("Usando el archivo más reciente: <comment>{$fileName}</comment>");
        }

        $filePath = is_file($fileName) ? $fileName : $backupsDir . DIRECTORY_SEPARATOR . basename($fileName);

        if (!file_exists($filePath)) {
            $this->error("El archivo no existe: {$filePath}");
            return 1;
        }

        $content = file_get_contents($filePath);
        $payload = json_decode($content, true);

        if (!$payload || !isset($payload['data'], $payload['iv'], $payload['tag'])) {
            $this->error("Formato de archivo inválido o dañado.");
            return 1;
        }

        $customKey = $this->option('key');
        $passphrase = $customKey ?: env('BACKUP_ENCRYPTION_KEY', config('app.key', 'CStreame-Master-Security-2026-SuperKey'));
        $derivedKey = hash('sha256', $passphrase, true);

        $iv = base64_decode($payload['iv']);
        $tag = base64_decode($payload['tag']);
        $ciphertext = base64_decode($payload['data']);

        // Verificar checksum
        if (isset($payload['checksum'])) {
            $currentChecksum = hash('sha256', $ciphertext);
            if ($currentChecksum !== $payload['checksum']) {
                $this->error("⚠️ ALERTA DE SEGURIDAD: El archivo fue alterado o está corrupto.");
                return 1;
            }
        }

        $decrypted = openssl_decrypt(
            $ciphertext,
            'aes-256-gcm',
            $derivedKey,
            OPENSSL_RAW_DATA,
            $iv,
            $tag,
            'cstreame-backup-aad'
        );

        if ($decrypted === false) {
            $this->error("❌ Error de autenticación: Clave maestra incorrecta o el archivo fue manipulado.");
            return 1;
        }

        $bundle = json_decode($decrypted, true);
        if (!$bundle || !isset($bundle['sql'])) {
            $this->error("El contenido descifrado no tiene el formato esperado.");
            return 1;
        }

        $outDir = $this->option('out') ?: $backupsDir . DIRECTORY_SEPARATOR . 'descifrados';
        if (!is_dir($outDir)) {
            @mkdir($outDir, 0755, true);
        }

        $baseName = pathinfo($filePath, PATHINFO_FILENAME);
        $sqlPath = $outDir . DIRECTORY_SEPARATOR . "{$baseName}_restaurable.sql";
        $jsonPath = $outDir . DIRECTORY_SEPARATOR . "{$baseName}_resumen.json";

        file_put_contents($sqlPath, $bundle['sql']);
        file_put_contents($jsonPath, json_encode($bundle['json'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $this->info("\n=========================================================");
        $this->info("   🔓 COPIA DESCIFRADA Y VERIFICADA CON ÉXITO            ");
        $this->info("=========================================================");
        $this->info("✓ Fecha del Respaldo : " . ($bundle['created_at'] ?? 'N/A'));
        $this->info("✓ Tablas extraídas   : " . ($bundle['tables_count'] ?? 0));
        $this->info("✓ Total de Registros : " . ($bundle['total_rows'] ?? 0));
        $this->info("✓ SQL generado       : {$sqlPath}");
        $this->info("✓ JSON legible       : {$jsonPath}");
        $this->line("<comment>Archivos listos para inspeccionar o restaurar.</comment>");

        return 0;
    }
}
