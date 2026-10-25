<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PDO;
use Throwable;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'cstreame:backup 
                            {--source=supabase : Base de datos a respaldar (supabase o local)}
                            {--path= : Carpeta personalizada de destino}';

    protected $description = 'Genera una copia de seguridad completa (.sql y .json) de todos los usuarios, revendedores, cuentas y ventas de Supabase o Local.';

    public function handle(): int
    {
        $this->info("=================================================");
        $this->info("    CSTREAME - GENERADOR DE COPIAS DE SEGURIDAD  ");
        $this->info("=================================================");

        $source = strtolower($this->option('source'));
        $targetDir = $this->option('path') ?: base_path('backups');

        if (!is_dir($targetDir)) {
            @mkdir($targetDir, 0755, true);
        }

        // Configuración de conexión
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

        // Obtener lista de tablas
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
        $sqlFileName = "cstreame_backup_{$source}_{$timestamp}.sql";
        $jsonFileName = "cstreame_resumen_{$source}_{$timestamp}.json";
        $sqlFilePath = $targetDir . DIRECTORY_SEPARATOR . $sqlFileName;
        $jsonFilePath = $targetDir . DIRECTORY_SEPARATOR . $jsonFileName;

        $sqlHandle = fopen($sqlFilePath, 'w');
        if (!$sqlHandle) {
            $this->error("No se pudo crear el archivo: {$sqlFilePath}");
            return 1;
        }

        // Encabezado SQL
        $header = "-- ==========================================================\n"
                . "-- CSTREAME BACKUP COMPLETO - " . date('Y-m-d H:i:s') . "\n"
                . "-- Fuente: " . strtoupper($source) . " ({$driver})\n"
                . "-- Tablas respaldadas: " . count($tables) . "\n"
                . "-- ==========================================================\n\n"
                . "BEGIN;\n\n";
        fwrite($sqlHandle, $header);

        $jsonData = [
            'backup_date' => date('Y-m-d H:i:s'),
            'source' => $source,
            'tables_count' => count($tables),
            'summary' => [],
            'data' => [],
        ];

        $totalRows = 0;
        $this->info("Respaldando tablas:");

        foreach ($tables as $table) {
            $query = ($driver === 'pgsql') ? "SELECT * FROM \"{$table}\"" : "SELECT * FROM `{$table}`";
            $stmt = $pdo->query($query);
            $rows = $stmt->fetchAll();
            $count = count($rows);
            $totalRows += $count;

            $jsonData['summary'][$table] = $count;
            $this->line("  • <info>{$table}</info>: {$count} registros");

            if ($count > 0) {
                // Guardar en JSON las tablas principales de negocio
                if (in_array($table, ['users', 'customers', 'sales', 'orders', 'services', 'service_plans', 'streaming_accounts', 'account_profiles', 'system_settings', 'reseller_plan_prices'])) {
                    $jsonData['data'][$table] = $rows;
                }

                fwrite($sqlHandle, "-- Datos de tabla: {$table} ({$count} registros)\n");

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

                    fwrite($sqlHandle, $insertSql);
                }
                fwrite($sqlHandle, "\n");
            }
        }

        fwrite($sqlHandle, "COMMIT;\n-- FIN DE LA COPIA DE SEGURIDAD\n");
        fclose($sqlHandle);

        // Guardar JSON
        file_put_contents($jsonFilePath, json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        $sqlSize = round(filesize($sqlFilePath) / 1024, 2);
        $jsonSize = round(filesize($jsonFilePath) / 1024, 2);

        $this->info("\n=================================================");
        $this->info("   COPIA DE SEGURIDAD COMPLETADA CON ÉXITO       ");
        $this->info("=================================================");
        $this->info("✓ Total de tablas respaldadas : " . count($tables));
        $this->info("✓ Total de registros guardados: {$totalRows}");
        $this->info("✓ Archivo SQL (para restaurar): {$sqlFileName} ({$sqlSize} KB)");
        $this->info("✓ Archivo JSON (resumen fácil): {$jsonFileName} ({$jsonSize} KB)");
        $this->info("✓ Ubicación en tu PC          : {$targetDir}");

        return 0;
    }
}
