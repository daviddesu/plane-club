<!DOCTYPE html>
<html data-theme="dark" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
        <link rel="icon" href="{{ asset('favicon.ico') }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            !function(t,e){var o,n,p,r;e.__SV||(window.posthog=e,e._i=[],e.init=function(i,s,a){function g(t,e){var o=e.split(".");2==o.length&&(t=t[o[0]],e=o[1]),t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}}(p=t.createElement("script")).type="text/javascript",p.async=!0,p.src=s.api_host.replace(".i.posthog.com","-assets.i.posthog.com")+"/static/array.js",(r=t.getElementsByTagName("script")[0]).parentNode.insertBefore(p,r);var u=e;for(void 0!==a?u=e[a]=[]:a="posthog",u.people=u.people||[],u.toString=function(t){var e="posthog";return"posthog"!==a&&(e+="."+a),t||(e+=" (stub)"),e},u.people.toString=function(){return u.toString(1)+".people (stub)"},o="init capture register register_once register_for_session unregister unregister_for_session getFeatureFlag getFeatureFlagPayload isFeatureEnabled reloadFeatureFlags updateEarlyAccessFeatureEnrollment getEarlyAccessFeatures on onFeatureFlags onSessionId getSurveys getActiveMatchingSurveys renderSurvey canRenderSurvey getNextSurveyStep identify setPersonProperties group resetGroups setPersonPropertiesForFlags resetPersonPropertiesForFlags setGroupPropertiesForFlags resetGroupPropertiesForFlags reset get_distinct_id getGroups get_session_id get_session_replay_url alias set_config startSessionRecording stopSessionRecording sessionRecordingStarted captureException loadToolbar get_property getSessionProperty createPersonProfile opt_in_capturing opt_out_capturing has_opted_in_capturing has_opted_out_capturing clear_opt_in_out_capturing debug".split(" "),n=0;n<o.length;n++)g(u,o[n]);e._i.push([i,s,a])},e.__SV=1)}(document,window.posthog||[]);
            posthog.init('phc_9P4bssfAQEZUcYEYjvQFrRcS3ApwrNTMDPQySGFlqAl',{api_host:'https://us.i.posthog.com', person_profiles: 'identified_only' // or 'always' to create profiles for anonymous users as well
                })
        </script>

        @if(!Auth::check())
        <script
            type="text/javascript"
            src="https://app.termly.io/resource-blocker/a569d0d4-62c0-484c-976d-d4892e3c0026?autoBlock=on"
            ></script>
        @endif

        <!-- Meta Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
            n.callMethod.apply(n,arguments):n.queue.push(arguments)};
            if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
            n.queue=[];t=b.createElement(e);t.async=!0;
            t.src=v;s=b.getElementsByTagName(e)[0];
            s.parentNode.insertBefore(t,s)}(window, document,'script',
            'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '1597335280484118');
            fbq('track', 'PageView');
            </script>
            <noscript><img height="1" width="1" style="display:none"
            src="https://www.facebook.com/tr?id=1597335280484118&ev=PageView&noscript=1"
            /></noscript>
            <!-- End Meta Pixel Code -->
                <!-- Google tag (gtag.js) -->
                <script async src="https://www.googletagmanager.com/gtag/js?id=AW-16786194088">
                </script>
                <script>
                    window.dataLayer = window.dataLayer || [];
                    function gtag(){dataLayer.push(arguments);}
                    gtag('js', new Date());

                    gtag('config', 'AW-16786194088');
                </script>
                <meta name="ezoic-site-verification" content="46InrUmUsKXP5FiEn6q3SXPMhI09EP" />
    </head>
    <body class="font-sans antialiased">

        {{-- The navbar with `sticky` and `full-width` --}}
        <x-mary-nav sticky full-width>

            <x-slot:brand>
                {{-- Drawer toggle for "main-drawer" --}}
                @if($user = Auth::user())
                    <label for="main-drawer" class="mr-3 lg:hidden">
                        <x-mary-icon name="o-bars-3" class="cursor-pointer" />
                    </label>
                @endif

                {{-- Brand --}}
                    <!-- Logo -->
                        <a href="/" wire:navigate.hover>
                            <img alt="Light Mode Logo" class="block w-auto h-12 fill-current [[data-theme=dark]_&]:hidden" src="/logo.png" />
                            <img alt="Dark Mode Logo" class="block w-auto h-12 fill-current dark:block [[data-theme=light]_&]:hidden" src="/logo-white.png" />
                        </a>
            </x-slot:brand>

            {{-- Right side actions --}}
            <x-slot:actions>
                <x-mary-theme-toggle class="btn-ghost btn-sm" />

            </x-slot:actions>
        </x-mary-nav>

        {{-- The main content with `full-width` --}}
        <x-mary-main with-nav full-width>
                {{-- This is a sidebar that works also as a drawer on small screens --}}
                {{-- Notice the `main-drawer` reference here --}}
                @if($user = Auth::user())
                    <x-slot:sidebar drawer="main-drawer" collapsible class="bg-base-200">
                        {{-- Activates the menu item when a route matches the `link` property --}}
                        <x-mary-menu activate-by-route>
                            <x-mary-menu-item title="Add sighting" icon="o-plus" link="/sighting/create" wire:navigate.hover  />
                            <x-mary-menu-separator />
                            <x-mary-menu-item title="Signtings" icon="o-camera" link="/sightings" wire:navigate.hover />
                            <x-mary-menu-separator />
                            <x-mary-menu-item icon="o-user" link="/profile" wire:navigate.hover>
                                {{ $user->name }}
                                @if($user->isPro())
                                    <x-mary-badge value="Pro" class="badge-primary" />
                                @else
                                    <x-mary-badge value="Free" class="badge-neutral" />
                                @endif
                            </x-mary-menu-item>
                                @if(!$user->isPro())
                                    <x-mary-button
                                        link="/checkout"
                                        class="mt-3 ml-2 btn-xs btn-primary"
                                        label="Upgrade"
                                        icon="o-arrow-up-circle"
                                        no-wire-navigate
                                    />
                                @endif
                            <x-mary-menu-separator />
                            <x-mary-menu-item title="Logout" icon="o-arrow-left-on-rectangle" link="/logout" no-wire-navigate />

                        </x-mary-menu>
                    </x-slot:sidebar>
                @endif
                {{-- The `$slot` goes here --}}
                <x-slot:content>
                    {{ $slot }}
                </x-slot:content>
        </x-mary-main>

        {{--  TOAST area --}}
        <x-mary-toast />
    </body>

</html>
