<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SystemSetting;

class PwaController extends Controller
{
    /**
     * Genera dinámicamente el manifest.json según la tienda/revendedor
     * Básico: Marca por defecto SPRO PLAY
     * PRO / Proveedor: Nombre y Logo propio del revendedor
     */
    public function manifest(Request $request)
    {
        $code = $request->query('ref') ?? $request->query('code');
        $seller = null;

        if ($code) {
            $seller = User::where('referral_code', $code)->first();
        }

        if (!$seller && auth()->check()) {
            $seller = auth()->user();
        }

        if ($seller && $seller->isReseller() && $seller->isPanelBasic()) {
            // PANEL BÁSICO: Marca oficial por defecto SPRO PLAY
            $name = 'SPRO PLAY Streaming';
            $shortName = 'SPRO PLAY';
            $icon192 = '/images/spro-play-icon-192.png';
            $icon512 = '/images/spro-play-icon-512.png';
            $iconSvg = '/images/spro-play-icon.svg';
            $themeColor = '#E50914';
            $startUrl = route('catalog.index', ['ref' => $seller->referral_code]);
        } elseif ($seller) {
            // PANEL PRO / PROVEEDOR / REVENDEDOR CON MARCA BLANCA
            $name = $seller->brand_name ?: 'CStreame Streaming';
            $shortName = $seller->brand_name ?: 'CStreame';
            $customLogo = $seller->brand_logo ? asset('storage/' . $seller->brand_logo) : null;
            $icon192 = $customLogo ?: '/images/cstreame-icon-192.png';
            $icon512 = $customLogo ?: '/images/cstreame-icon-512.png';
            $iconSvg = '/images/cstreame-icon.svg';
            $themeColor = $seller->brand_primary_color ?: '#E50914';
            $startUrl = route('catalog.index', ['ref' => $seller->referral_code]);
        } else {
            // SUPER ADMIN / MASTER PLATFORM
            $name = 'CStreame Master Platform';
            $shortName = 'CStreame';
            $icon192 = '/images/cstreame-icon-192.png';
            $icon512 = '/images/cstreame-icon-512.png';
            $iconSvg = '/images/cstreame-icon.svg';
            $themeColor = '#E50914';
            $startUrl = '/';
        }

        $manifest = [
            'id' => $startUrl,
            'name' => $name,
            'short_name' => $shortName,
            'description' => "Acceso directo e instalación de {$name}. Catálogo de streaming y Bóveda privada con PIN y biometría.",
            'start_url' => $startUrl,
            'scope' => '/',
            'display' => 'standalone',
            'orientation' => 'portrait',
            'background_color' => '#0b0f19',
            'theme_color' => $themeColor,
            'prefer_related_applications' => false,
            'categories' => ['entertainment', 'utilities'],
            'icons' => [
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => $icon192,
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any'
                ],
                [
                    'src' => $icon512,
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable'
                ],
                [
                    'src' => $iconSvg,
                    'sizes' => 'any',
                    'type' => 'image/svg+xml',
                    'purpose' => 'any'
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json; charset=utf-8')
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Cache-Control', 'public, max-age=600');
    }

    /**
     * Página corta de instalación de la PWA para cualquier dispositivo (Android, iOS, Windows, Mac)
     * URL corta: /app/{code?} o /instalar/{code?}
     * Permite compartir directamente por WhatsApp con un solo clic.
     */
    public function installPage(Request $request, $code = null)
    {
        $code = $code ?? $request->query('ref');
        $seller = null;

        if ($code) {
            $seller = User::where('referral_code', $code)->first();
        }

        if (!$seller) {
            $seller = User::where('role', 'admin')->first();
        }

        // Definición de marca según plan de panel
        if ($seller && $seller->isReseller() && $seller->isPanelBasic()) {
            $brandName = 'SPRO PLAY';
            $brandLogo = asset('images/spro-play-logo.svg');
            $brandIcon = asset('images/spro-play-icon.svg');
            $isBasic = true;
        } else {
            $brandName = $seller ? ($seller->brand_name ?: ($seller->isAdmin() ? 'CStreame Master Platform' : $seller->name)) : 'CStreame';
            $brandLogo = ($seller && $seller->brand_logo) ? asset('storage/' . $seller->brand_logo) : asset('images/cstreame-icon.svg');
            $brandIcon = ($seller && $seller->brand_logo) ? asset('storage/' . $seller->brand_logo) : asset('images/cstreame-icon.svg');
            $isBasic = false;
        }

        $primaryColor = $seller ? ($seller->brand_primary_color ?: '#E50914') : '#E50914';
        $accentColor = $seller ? ($seller->brand_accent_color ?: '#06b6d4') : '#06b6d4';
        $supportWhatsapp = $seller ? $seller->resolved_vault_whatsapp : null;

        // URL corta de instalación para compartir por WhatsApp
        $shareUrl = $seller && $seller->referral_code 
            ? url('/app/' . $seller->referral_code) 
            : url('/app');

        // Catálogo web link
        $catalogUrl = $seller && $seller->referral_code 
            ? route('catalog.index', ['ref' => $seller->referral_code]) 
            : route('catalog.index');

        // Bóveda link
        $vaultUrl = route('boveda.login');

        // Mensaje preformateado para compartir por WhatsApp
        $whatsappShareText = "📲 ¡Instala la App oficial de *{$brandName}* en tu celular o computadora!\n\n"
            . "✨ Catálogo de cuentas y pantallas Netflix, Disney, Max y más.\n"
            . "🔐 Acceso seguro a tu Bóveda de cliente con PIN y Biometría.\n\n"
            . "👉 Toca aquí para instalar en 1 clic:\n" . $shareUrl;

        $whatsappShareUrl = "https://wa.me/?text=" . urlencode($whatsappShareText);

        return view('pwa.install', compact(
            'seller',
            'brandName',
            'brandLogo',
            'brandIcon',
            'isBasic',
            'primaryColor',
            'accentColor',
            'supportWhatsapp',
            'shareUrl',
            'catalogUrl',
            'vaultUrl',
            'whatsappShareUrl'
        ));
    }
}
