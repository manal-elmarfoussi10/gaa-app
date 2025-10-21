@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto mt-8">
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="pencil" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Modifier le pack d’unités</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                <div class="font-semibold mb-1">Veuillez corriger les erreurs :</div>
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @include('superadmin.units.packages._form', ['package' => $package])
    </div>
</div>
@endsection