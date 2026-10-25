<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended', 'pending') NOT NULL DEFAULT 'active'");
        } elseif ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'suspended', 'pending'))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = \Illuminate\Support\Facades\DB::getDriverName();
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users MODIFY COLUMN status ENUM('active', 'suspended') NOT NULL DEFAULT 'active'");
        } elseif ($driver === 'pgsql') {
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users DROP CONSTRAINT IF EXISTS users_status_check");
            \Illuminate\Support\Facades\DB::statement("ALTER TABLE users ADD CONSTRAINT users_status_check CHECK (status IN ('active', 'suspended'))");
        }
    }
};
