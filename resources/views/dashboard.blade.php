@extends('layouts.app')

@section('content')
    <div class="d-flex flex-column h-100">

        <!-- 1. Header & Form Pencarian -->
        @include('partials.header')

        @if(isset($country))
            <!-- 2. Profil Negara (Blok 1) -->
            @include('partials.profile')

            <!-- 3. Metrics/Kartu Data (Blok 2) -->
            @include('partials.metrics')

            <!-- 4. Visuals/Grafik & Berita (Blok 3) -->
            @include('partials.visuals')
            
            <!-- 5. Modals (Detail Negara & Detail Cuaca) -->
            @include('partials.modals')
        @endif

    </div>

    <!-- SCRIPT SELECT2 -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        let waitForJQuery = setInterval(function () {
            if (typeof window.jQuery !== 'undefined') {
                clearInterval(waitForJQuery);
                const $ = window.jQuery;
                if ($.fn.select2) {
                    $('.select2-country').select2({ 
                        width: '100%',
                        theme: "bootstrap-5" 
                    });
                }
            }
        }, 100);
    });
    </script>
@endsection