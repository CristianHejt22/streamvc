<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Throwable;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'cstreame:backup 
                            {--source=supabase : Base de datos a respaldar (supabase o local)}
                            {--path= : Carpeta personalizada de destino}
                            {--key= : Clave maestra de cifrado personalizada (opcional)}
                            {--no-encrypt : Desactivar cifrado (no recomendado)}';

    protected $description = 'Genera una copia de seguridad con cifrado militar AES-256-GCM de todos los usuarios, revendedores, cuentas y ventas.';

    public function handle(): int
    {
        $this->info("=========================================================");
        $this->info("   CSTREAME - BACKUP DE PROTECCIÓN EXTREMA (AES-256-GCM) ");
        $this->info("=========================================================");

        $source = strtolower($this->option('source'));
        $targetDir = $this->option('path') ?: base_path('backups');
        $encrypt = !$this->option('no-encrypt');
        $customKey = $this->option('key');

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // Conectar a la base de datos
        if ($source === 'supabase') {
            $host = env('SUPABASE_DB_HOST', 'aws-0-us-east-1.pooler.supabase.com');
            $port = env('SUPABASE_DB_PORT', '5432');
            $dbname = env('SUPABASE_DB_DATABASE', 'postgres');
            $user = env('SUPABASE_DB_USERNAME', 'postgres.ysdeyhdugaxvorwzdwqm');
            $password = env('SUPABASE_DB_PASSWORD', 'BocaCampeon2026');
            $driver = 'pgsql';
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbname};sslmode=require";
            $this->line("<comment>Conectando a Supabase PostgreSQL ({$host})...</comment>");
        } else {
            $driver = config('database.default');
            $host = config("database.connections.{$driver}.host");
            $port = config("database.connections.{$driver}.port");
            $dbname = config("database.connections.{$driver}.database");
            $user = config("database.connections.{$driver}.username");
            $password = config("database.connections.{$driver}.password");
            $dsn = "{$driver}:host={$host};port={$port};dbname={$dbname}";
            $this->line("<comment>Conectando a base local ({$driver} en {$host})...</comment>");
        }

        try {
            $pdo = new PDO($dsn, $user, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_TIMEOUT => 15,
            ]);
        } catch (Throwable $e) {
            $this->error("Error al conectar a la base de datos: " . $e->getMessage());
            return 1;
        }

        $this->info("✓ Conexión establecida con éxito.");

        // Obtener tablas
        if ($driver === 'pgsql') {
            $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' ORDER BY table_name");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        } else {
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        if (empty($tables)) {
            $this->warn("No se encontraron tablas para respaldar.");
            return 0;
        }

        $timestamp = date('Y-m-d_H-i-s');
        $rawSql = "-- ==========================================================\n"
                . "-- CSTREAME BACKUP PROTEGIDO - " . date('Y-m-d H:i:s') . "\n"
                . "-- Fuente: " . strtoupper($source) . " ({$driver})\n"
                . "-- Tablas: " . count($tables) . "\n"
                . "-- ==========================================================\n\n"
                . "BEGIN;\n\n";

        $jsonData = [
            'backup_timestamp' => time(),
            'backup_date' => date('Y-m-d H:i:s'),
            'source' => $source,
            'driver' => $driver,
            'tables_count' => count($tables),
            'summary' => [],
            'data' => [],
        ];

        $totalRows = 0;
        $this->info("Extrayendo datos de la base de datos:");

        foreach ($tables as $table) {
            $query = ($driver === 'pgsql') ? "SELECT * FROM \"{$table}\"" : "SELECT * FROM `{$table}`";
            $stmt = $pdo->query($query);
            $rows = $stmt->fetchAll();
            $count = count($rows);
            $totalRows += $count;

            $jsonData['summary'][$table] = $count;
            $this->line("  • <info>{$table}</info>: {$count} registros");

            if ($count > 0) {
                // Guardar en JSON tablas esenciales de negocio
                if (in_array($table, ['users', 'customers', 'sales', 'orders', 'services', 'service_plans', 'streaming_accounts', 'account_profiles', 'system_settings', 'reseller_plan_prices', 'renewal_requests'])) {
                    $jsonData['data'][$table] = $rows;
                }

                $rawSql .= "-- Tabla: {$table} ({$count} registros)\n";
                $firstRow = $rows[0];
                $columns = array_keys($firstRow);
                $escapedCols = array_map(function ($c) use ($driver) {
                    return ($driver === 'pgsql') ? "\"{$c}\"" : "`{$c}`";
                }, $columns);
                $colsSql = implode(', ', $escapedCols);

                foreach (array_chunk($rows, 50) as $chunk) {
                    $valuesArr = [];
                    foreach ($chunk as $row) {
                        $rowVals = [];
                        foreach ($columns as $col) {
                            $val = $row[$col];
                            if ($val === null) {
                                $rowVals[] = 'NULL';
                            } elseif (is_bool($val)) {
                                $rowVals[] = $val ? 'TRUE' : 'FALSE';
                            } elseif (is_numeric($val) && !is_string($val)) {
                                $rowVals[] = $val;
                            } else {
                                $rowVals[] = $pdo->quote((string)$val);
                            }
                        }
                        $valuesArr[] = "(" . implode(', ', $rowVals) . ")";
                    }

                    $insertSql = ($driver === 'pgsql')
                        ? "INSERT INTO \"{$table}\" ({$colsSql}) VALUES\n  " . implode(",\n  ", $valuesArr) . ";\n"
                        : "INSERT INTO `{$table}` ({$colsSql}) VALUES\n  " . implode(",\n  ", $valuesArr) . ";\n";

                    $rawSql .= $insertSql;
                }
                $rawSql .= "\n";
            }
        }

        $rawSql .= "COMMIT;\n-- FIN DEL RESPALDO\n";
        $rawJson = json_encode($jsonData, JSON_UNESCAPED_UNICODE);

        // Paquete combinado
        $bundle = [
            'version' => '1.0',
            'created_at' => date('c'),
            'source' => $source,
            'tables_count' => count($tables),
            'total_rows' => $totalRows,
            'sql' => $rawSql,
            'json' => $jsonData,
        ];

        $bundleString = json_encode($bundle);

        if ($encrypt) {
            // Clave de derivación AES-256
            $passphrase = $customKey ?: env('BACKUP_ENCRYPTION_KEY', config('app.key', 'CStreame-Master-Security-2026-SuperKey'));
            // Derivar clave de 32 bytes con SHA-256
            $derivedKey = hash('sha256', $passphrase, true);

            // Generar vector de inicialización (IV) criptográfico aleatorio de 12 bytes para GCM
            $iv = openssl_random_pseudo_bytes(12);
            $tag = '';

            $ciphertext = openssl_encrypt(
                $bundleString,
                'aes-256-gcm',
                $derivedKey,
                OPENSSL_RAW_DATA,
                $iv,
                $tag,
                'cstreame-backup-aad'
            );

            if ($ciphertext === false) {
                $this->error("Error crítico durante el cifrado AES-256.");
                return 1;
            }

            // Estructura del archivo cifrado binario protegido
            $payload = [
                'cipher' => 'AES-256-GCM',
                'iv' => base64_encode($iv),
                'tag' => base64_encode($tag),
                'checksum' => hash('sha256', $ciphertext),
                'data' => base64_encode($ciphertext),
            ];

            $outFileName = "cstreame_backup_{$source}_{$timestamp}.cstbackup";
            $outFilePath = $targetDir . DIRECTORY_SEPARATOR . $outFileName;

            file_put_contents($outFilePath, json_encode($payload, JSON_PRETTY_PRINT));
            $fileSize = round(filesize($outFilePath) / 1024, 2);

            $this->info("\n=========================================================");
            $this->info("   🔒 COPIA CIFRADA CON ÉXITO (PROTECCIÓN EXTREMA)       ");
            $this->info("=========================================================");
            $this->info("✓ Algoritmo de Cifrado : AES-256-GCM (Grado Militar)");
            $this->info("✓ Autenticación (Tag)  : 128-bit Anti-Manipulación");
            $this->info("✓ Archivo Generado     : {$outFileName} ({$fileSize} KB)");
            $this->info("✓ Ubicación en tu PC   : {$targetDir}");
            $this->line("<comment>⚠️  Ningún dato está en texto plano. Aunque alguien robe este archivo, es 100% ilegible sin tu clave maestra.</comment>");
        } else {
            $sqlFileName = "cstreame_backup_{$source}_{$timestamp}.sql";
            $jsonFileName = "cstreame_resumen_{$source}_{$timestamp}.json";
            file_put_contents($targetDir . DIRECTORY_SEPARATOR . $sqlFileName, $rawSql);
            file_put_contents($targetDir . DIRECTORY_SEPARATOR . $jsonFileName, $rawJson);
            $this->warn("⚠️  Copia generada SIN cifrado.");
        }

        return 0;
    }
}
