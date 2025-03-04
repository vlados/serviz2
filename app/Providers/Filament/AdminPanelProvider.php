<?php

namespace App\Providers\Filament;

use App\Filament\Resources\CustomerResource;
use App\Filament\Resources\ScooterResource;
use App\Filament\Resources\ServiceOrderResource;
use App\Filament\Resources\SparePartResource;
use App\Filament\Widgets\LatestServiceOrders;
use App\Filament\Widgets\ServiceOrdersChart;
use App\Filament\Widgets\ServiceStatsOverview;
use App\Providers\Filament\GlobalSearchProvider;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Enums\MaxWidth;
use TomatoPHP\FilamentPWA\FilamentPWAPlugin;
use Kainiklas\FilamentScout\FilamentScoutPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Blue,
                'danger' => Color::Red,
                'info' => Color::Cyan,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
            ])
            ->brandName('Сервиз')
            ->favicon(asset('favicon.ico'))
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                ServiceStatsOverview::class,
                ServiceOrdersChart::class,
                LatestServiceOrders::class,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Клиенти и Тротинетки'),
                NavigationGroup::make()
                    ->label('Управление на Сервиза'),
            ])
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                QuickCreatePlugin::make()
                    ->label('Бързо създаване'),
                FilamentPWAPlugin::make()
                    ->allowPWASettings(false),
                FilamentScoutPlugin::make(),
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->maxContentWidth(MaxWidth::Full)
            ->font('')
            ->globalSearch(true)
            ->globalSearchDebounce(100)
            ->sidebarWidth("250px")

            ->viteTheme('resources/css/filament/admin/theme.css')
            ->sidebarCollapsibleOnDesktop()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
