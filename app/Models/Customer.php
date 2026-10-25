<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'user_id',
        'customer_code',
        'name',
        'phone',
        'email',
        'password',
        'status',
        'country',
        'anti_phishing_phrase',
        'security_pin',
        'biometric_credential_id',
        'biometric_public_key',
        'notes',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($customer) {
            if (empty($customer->customer_code)) {
                $customer->customer_code = 'CLI-' . rand(1000, 9999);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function activeSales()
    {
        return $this->sales()->whereIn('status', ['active', 'expiring_soon']);
    }

    public function renewalRequests()
    {
        return $this->hasMany(RenewalRequest::class);
    }

    // Limpia el número telefónico para WhatsApp API
    public function getCleanPhoneAttribute(): ?string
    {
        if (!$this->phone) return null;
        return preg_replace('/[^0-9]/', '', $this->phone);
    }

    /**
     * Localiza un cliente por email o por teléfono de manera flexible (con o sin espacios, guiones o código de país)
     */
    public static function findByInput(?string $input): ?self
    {
        if (empty($input)) return null;
        $input = trim($input);

        // 1. Coincidencia directa por email o teléfono exacto
        $customer = static::where('email', $input)
            ->orWhere('phone', $input)
            ->first();

        if ($customer) return $customer;

        // 2. Coincidencia numérica flexible por dígitos
        $clean = preg_replace('/[^0-9]/', '', $input);
        if (!empty($clean) && strlen($clean) >= 6) {
            $last7 = substr($clean, -7);
            $candidates = static::where('phone', 'like', "%{$last7}%")
                ->orWhere('phone', 'like', "%{$clean}%")
                ->get();

            foreach ($candidates as $cand) {
                $candClean = preg_replace('/[^0-9]/', '', $cand->phone);
                if ($candClean && (str_contains($candClean, $clean) || str_contains($clean, $candClean))) {
                    return $cand;
                }
            }

            // Fallback global en memoria
            return static::all()->first(function ($cand) use ($clean) {
                $candClean = preg_replace('/[^0-9]/', '', $cand->phone);
                return $candClean && (str_contains($candClean, $clean) || str_contains($clean, $candClean));
            });
        }

        return null;
    }
}
