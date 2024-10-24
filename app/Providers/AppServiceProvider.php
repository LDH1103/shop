<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use SocialiteProviders\Manager\SocialiteProviders;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (\SocialiteProviders\Manager\SocialiteWasCalled $event) {
            // 카카오
            $event->extendSocialite('kakao', \SocialiteProviders\Kakao\KakaoProvider::class);
            // 네이버
            $event->extendSocialite('naver', \SocialiteProviders\Naver\Provider::class);
        });
    }
}
