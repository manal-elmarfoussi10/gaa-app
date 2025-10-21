@extends('layout')

@section('content')
<div class="max-w-3xl mx-auto mt-8">
    <div class="bg-white rounded-2xl shadow p-6 md:p-8">
        <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 rounded-xl bg-[#FFF1EC] flex items-center justify-center">
                <i data-lucide="plus-circle" class="w-5 h-5 text-[#FF4B00]"></i>
            </div>
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Créditer des unités (manuel)</h1>
        </div>

        @if(session('success'))
            <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-3">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-3">
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('superadmin.units.credits.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Société</label>
                <select name="company_id" class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-200">
                    @foreach($companies as $c)
                        <option value="{{ $c->id }}" @selected(old('company_id')==$c->id)>
                            {{ $c->name }} — {{ $c->units }} unité(s)
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Unités à créditer</label>
                    <input type="number" min="1" name="units" value="{{ old('units', 10) }}"
                           class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-200" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                    <input type="text" value="manuel" disabled
                           class="w-full border rounded-lg px-3 py-2 bg-gray-50 text-gray-600">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Note (optionnel)</label>
                <textarea name="note" rows="3"
                          class="w-full border rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-orange-200"
                          placeholder="Précisez la raison de l'ajustement...">{{ old('note') }}</textarea>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button class="inline-flex items-center gap-2 bg-[#FF4B00] text-white px-4 py-2 rounded-lg hover:bg-orange-600">
                    <i data-lucide="check" class="w-4 h-4"></i> Créditer
                </button>
                <a href="{{ route('superadmin.units.packages.index') }}" class="px-4 py-2 rounded-lg border hover:bg-gray-50">Retour</a>
            </div>
        </form>
    </div>
</div>
@endsection