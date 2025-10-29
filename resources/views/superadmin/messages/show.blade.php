@extends('layout')
@section('title', 'Message')

@section('content')
<div class="px-6 py-6">
  <div class="max-w-3xl mx-auto space-y-6">

    <a href="{{ route('superadmin.messages.index') }}"
       class="inline-flex items-center gap-2 text-cyan-700 hover:text-cyan-900">
      <i data-lucide="arrow-left" class="w-4 h-4"></i> Retour
    </a>

    <div class="bg-white rounded-2xl shadow-sm border p-6">
      <div class="flex items-start justify-between">
        <div>
          <h1 class="text-xl font-semibold text-gray-800 flex items-center gap-2">
            <i data-lucide="mail" class="w-5 h-5 text-[#FF4B00]"></i>
            Message
          </h1>
          <p class="text-gray-500 mt-1">
            Reçu le {{ $message->created_at?->format('d/m/Y H:i') }}
          </p>
        </div>
        <form action="{{ route('superadmin.messages.destroy', $message) }}" method="POST"
              onsubmit="return confirm('Supprimer ce message ?')">
          @csrf
          @method('DELETE')
          <button type="submit"
                  class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50 text-red-700 hover:bg-red-100">
            <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
          </button>
        </form>
      </div>

      <div class="mt-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <p class="text-xs text-gray-500">Nom</p>
          <p class="font-medium">{{ $message->name }}</p>
        </div>
        <div>
          <p class="text-xs text-gray-500">Email</p>
          <p class="font-medium">
            <a class="text-cyan-700 hover:text-cyan-900" href="mailto:{{ $message->email }}">{{ $message->email }}</a>
          </p>
        </div>
        <div>
          <p class="text-xs text-gray-500">Type</p>
          <p class="font-medium">
            <span class="px-2 py-1 text-xs font-medium rounded-full
              @if($message->type === 'general') bg-blue-100 text-blue-800
              @elseif($message->type === 'demo') bg-green-100 text-green-800
              @elseif($message->type === 'partner') bg-purple-100 text-purple-800
              @else bg-gray-100 text-gray-800 @endif">
              {{ ucfirst($message->type ?? 'general') }}
            </span>
          </p>
        </div>
        @if(isset($message->company_id))
          <div class="md:col-span-2">
            <p class="text-xs text-gray-500">Société</p>
            <p class="font-medium">{{ $message->company->name ?? '—' }}</p>
          </div>
        @endif
      </div>

      <div class="mt-6">
        <p class="text-xs text-gray-500 mb-1">Message</p>
        <div class="rounded-lg border bg-gray-50 p-4 text-gray-800 whitespace-pre-wrap">
          {{ $message->message }}
        </div>
      </div>
    </div>

  </div>
</div>
<script>lucide.createIcons();</script>
@endsection