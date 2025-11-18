<!DOCTYPE html>
<html class="h-full" data-kt-theme="true" data-kt-theme-mode="light" dir="ltr" lang="id">

<head>

    <title>
        @yield('title') | {{ config('app.name') }}
    </title>
    <meta charset="utf-8" />
    <meta content="follow, index" name="robots" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta content="@yield('description', 'Dasbor modern yang dibangun dengan Laravel dan Metronic')"
        name="description" />
    <meta content="@yield('title') | {{ config('app.name') }}" name="twitter:title" />
    <meta content="@yield('description', 'Dasbor modern yang dibangun dengan Laravel dan Metronic')"
        name="twitter:description" />

    <meta content="{{ url()->current() }}" property="og:url" />
    <meta content="id_ID" property="og:locale" />
    <meta content="website" property="og:type" />
    <meta content="{{ config('app.name') }}" property="og:site_name" />
    <meta content="@yield('title') | {{ config('app.name') }}" property="og:title" />
    <meta content="@yield('description', 'Modern dashboard built with Laravel and Metronic')"
        property="og:description" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
        rel="stylesheet">
    <link href="{{ asset('assets/media/app/icon.png') }}" rel="shortcut icon" />

    <link href="{{ asset('assets/vendors/apexcharts/apexcharts.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/vendors/keenicons/styles.bundle.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/css/styles.css') }}" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/css/mobile-grid.css', 'resources/js/app.js'])

    <link id="themeLink" href="{{ asset('assets/vendors/kendoui/styles/bootstrap-4.css') }}" rel="stylesheet" />

    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }

        /* Mobile-specific height fixes */
        @media (max-width: 1023px) {

            html,
            body {
                height: 100vh;
                max-height: 100vh;
                overflow: hidden;
            }

            .flex.grow {
                height: 100vh;
                max-height: 100vh;
            }

            #scrollable_content {
                height: calc(100vh - 60px);
                /* Account for header height */
                max-height: calc(100vh - 60px);
                overflow-y: auto;
            }
        }

        @media (max-width: 1024px) {

            /* Fix main content overflow on mobile */
            main {
                overflow-x: hidden !important;
            }

            /* Ensure proper padding on mobile for kt-container */
            .kt-container-fixed {
                padding-left: 1rem !important;
                padding-right: 1rem !important;
            }

            /* Ensure cards are properly constrained on mobile */
            .kt-card {
                max-width: 100% !important;
                overflow-x: hidden !important;
            }

            .kt-card-content {
                max-width: 100% !important;
                overflow-x: hidden !important;
                box-sizing: border-box !important;
            }

            /* Ensure grid wrapper is contained */
            #grid {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            /* Force grid to respect container width */
            #grid .k-grid {
                width: 100% !important;
                max-width: 100% !important;
            }

            /* Enable horizontal scrolling on grid content */
            #grid .k-grid-content {
                overflow-x: auto !important;
                -webkit-overflow-scrolling: touch;
            }

            /* Make locked columns container scrollable */
            #grid .k-grid-container {
                overflow-x: auto !important;
            }
        }
    </style>
    @stack('styles')
</head>

<body
    class="antialiased flex h-screen lg:h-full text-base text-foreground bg-background [--header-height:60px] [--sidebar-width:270px] overflow-hidden lg:overflow-hidden bg-mono dark:bg-background">

    <!-- Theme Mode -->
    <script>
        const defaultThemeMode = 'light';
        let themeMode;

        if (document.documentElement) {
            if (localStorage.getItem('kt-theme')) {
                themeMode = localStorage.getItem('kt-theme');
            } else if (document.documentElement.hasAttribute('data-kt-theme-mode')) {
                themeMode = document.documentElement.getAttribute('data-kt-theme-mode');
            } else {
                themeMode = defaultThemeMode;
            }

            if (themeMode === 'system') {
                themeMode = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }

            document.documentElement.classList.add(themeMode);
            document.getElementById('themeLink').href = `{{ asset('assets/vendors/kendoui/styles/bootstrap-') }}${themeMode === 'dark' ? '4-dark' : '4'}.css`;
        }
    </script>
    <!-- End of Theme Mode -->

    <!-- Page -->
    <div class="flex grow h-full lg:h-auto">
        @include('partials.header')

        <!-- Wrapper -->
        <div class="flex flex-col lg:flex-row grow pt-(--header-height) lg:pt-0 h-full lg:h-auto">
            @include('partials.sidebar')

            <!-- Main -->
            <div
                class="flex flex-col grow lg:rounded-l-xl bg-background border border-input lg:ms-(--sidebar-width) h-full lg:h-auto">
                <div class="flex flex-col grow kt-scrollable-y-auto lg:[--kt-scrollbar-width:auto] pt-5 h-full lg:h-auto overflow-y-auto lg:overflow-y-auto"
                    id="scrollable_content">
                    <main class="grow" role="content">
                        @if (isset($breadcrumbs) && count($breadcrumbs) > 0)
                            <!-- Toolbar -->
                            <div class="pb-5">
                                <!-- Container -->
                                <div class="kt-container-fixed flex items-center justify-between flex-wrap gap-3">
                                    <div class="flex flex-col flex-wrap gap-1">
                                        <h1 class="font-medium text-lg text-mono">
                                            {{ end($breadcrumbs)->title }}
                                        </h1>
                                        <div class="flex items-center gap-1 text-sm font-normal">
                                            @if (count($breadcrumbs) > 1)
                                                @foreach ($breadcrumbs as $breadcrumb)
                                                    @if (!$loop->first)
                                                        <span class="text-muted-foreground text-sm">
                                                            /
                                                        </span>
                                                    @endif
                                                    @if ($loop->last)
                                                        {{-- Last breadcrumb - not clickable --}}
                                                        <span class="text-secondary-foreground">
                                                            {{ $breadcrumb->title }}
                                                        </span>
                                                    @else
                                                        {{-- Non-last breadcrumb - clickable if has valid link --}}
                                                        @if ($breadcrumb->link && $breadcrumb->link !== '#')
                                                            <a href="{{ $breadcrumb->link }}"
                                                                class="text-secondary-foreground hover:text-primary transition-colors duration-200">
                                                                {{ $breadcrumb->title }}
                                                            </a>
                                                        @else
                                                            <span class="text-secondary-foreground">
                                                                {{ $breadcrumb->title }}
                                                            </span>
                                                        @endif
                                                    @endif
                                                @endforeach
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        @yield('toolbar-actions')
                                    </div>
                                </div>
                                <!-- End of Container -->
                            </div>
                            <!-- End of Toolbar -->
                        @endif
                        @yield('content')
                    </main>
                    @include('partials.footer')
                </div>
            </div>
            <!-- End of Main -->
        </div>
        <!-- End of Wrapper -->
    </div>
    <!-- End of Page -->

    @include('partials.modals')

    <!-- Scripts -->
    <script src="{{ asset('assets/js/core.bundle.js') }}"></script>
    <script src="{{ asset('assets/vendors/ktui/ktui.min.js') }}"></script>
    <script src="{{ asset('assets/js/widgets/general.js') }}"></script>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="{{ asset('assets/js/axios.min.js') }}"></script>
    <script src="{{ asset('assets/js/moment.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/kendoui/js/kendo.all.min.js') }}"></script>
    <script src="{{ asset('assets/vendors/kendoui/js/messages/kendo.messages.id-ID.min.js') }}"></script>
    <script src="{{ asset('assets/js/kendo-dialog-helper.js') }}"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
        axios.defaults.headers.common['X-CSRF-TOKEN'] = $('meta[name="csrf-token"]').attr('content');

        KendoLicensing.setScriptKey(
            "{{ config('app.kendo_grid_license_key') }}"
        );
    </script>

    <script type="module">
        // Import the functions you need from the SDKs you need
        import { initializeApp } from "https://www.gstatic.com/firebasejs/12.4.0/firebase-app.js";
        import { getAnalytics } from "https://www.gstatic.com/firebasejs/12.4.0/firebase-analytics.js";
        import { getMessaging, getToken } from "https://www.gstatic.com/firebasejs/12.4.0/firebase-messaging.js";

        // Your web app's Firebase configuration
        // For Firebase JS SDK v7.20.0 and later, measurementId is optional
        const firebaseConfig = {
            apiKey: "AIzaSyBr9YSH7Vyh8Ho9j_1idE37cZx5oBF4atU",
            authDomain: "language-ai-458304.firebaseapp.com",
            projectId: "language-ai-458304",
            storageBucket: "language-ai-458304.firebasestorage.app",
            messagingSenderId: "60214587876",
            appId: "1:60214587876:web:080b0b589e773e8a7cd7f1"
        };

        // Initialize Firebase
        const app = initializeApp(firebaseConfig);
        const analytics = getAnalytics(app);
        const messaging = getMessaging(app);

        // Function to request notification permission and get FCM token
        async function initializeNotifications() {
            try {
                // Check if service workers are supported
                if (!('serviceWorker' in navigator)) {
                    console.log('Service Workers not supported');
                    return;
                }

                // Register service worker
                await navigator.serviceWorker.register('/firebase-messaging-sw.js');

                // Request notification permission
                const permission = await Notification.requestPermission();

                if (permission === 'granted') {
                    // Get FCM token
                    const token = await getToken(messaging, {
                        vapidKey: 'BP4YBAc-n6vveO4qnjBZdNyjdy5fLsWQStgy-vQkyCBvZ-8GpdxUGxYInldDDju7kYNAoICeUNtPnS6oXnleejM'
                    });

                    if (token) {
                        // Send token to backend
                        const response = await axios.post('{{ route("notification.storeToken") }}', {
                            token: token
                        });

                        if (response.data.code === 'SUCCESS') {
                            console.log('FCM token synced successfully');
                        } else {
                            console.log('Failed to sync token:', response.data.message);
                        }
                    }
                } else {
                    console.log('Notification permission denied');
                }
            } catch (error) {
                // Silently fail - don't show error to user
                console.log('Notification setup error:', error);
            }
        }

        // Initialize notifications when DOM is ready
        document.addEventListener('DOMContentLoaded', function () {
            initializeNotifications();
        });

    </script>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            $("#loading").fadeOut();

            // Watch for theme changes
            const themeToggle = document.querySelector('[data-kt-theme-switch-toggle="true"]');
            if (themeToggle) {
                themeToggle.addEventListener('change', function () {
                    const isDark = this.checked;
                    window.themeMode = isDark ? 'dark' : 'light';
                    document.getElementById('themeLink').href =
                        `{{ asset('assets/vendors/kendoui/styles/bootstrap-') }}${isDark ? '4-dark' : '4'}.css`;
                });
            }
        });
    </script>

    @stack('scripts')

    @include('partials._searchMenu')
</body>

</html>
