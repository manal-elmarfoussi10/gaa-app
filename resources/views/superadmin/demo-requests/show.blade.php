@extends('layout')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Détails de la demande — Super Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #FF4B00;
            --primary-light: #ff7b40;
            --primary-extra-light: #fff1ec;
            --success: #10b981;
            --warning: #f59e0b;
        }
        .custom-dashboard-container { opacity:0; transform: translateY(20px); animation: fadeIn .8s ease forwards; }
        @keyframes fadeIn { to { opacity:1; transform: translateY(0); } }
        .info-card { background: white; border-radius: 12px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,.1); margin-bottom: 24px; }
        .info-label { font-weight: 600; color: #374151; margin-bottom: 4px; }
        .info-value { color: #6b7280; }
        .no-anim { opacity: 1 !important; transform: none !important; animation: none !important; }
        @media print {.custom-dashboard-container{opacity:1!important;transform:none!important;animation:none!important}}
    </style>
</head>

<body class="bg-gradient-to-br from-gray-100 to-gray-200 min-h-screen p-6">
    <div class="container mx-auto px-4 py-6 custom-dashboard-container">
        {{-- Header --}}
        <div class="flex justify-between items-center py-5 mb-6 border-b border-gray-300">
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800 flex items-center gap-3 transition-transform hover:scale-102">
                <i class="fas fa-user-plus"></i>
                Détails de la <span class="text-orange-500">Demande</span>
            </h1>
            <div class="flex gap-4">
                <a href="{{ route('superadmin.demo-requests.index') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold border border-gray-300 text-gray-700 hover:bg-gray-100 hover:border-gray-500 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i> Retour à la liste
                </a>
                <form method="POST" action="{{ route('superadmin.demo-requests.activate', $user->id) }}"
                      class="inline"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir activer ce compte ?')">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold bg-green-500 text-white hover:bg-green-600 hover:-translate-y-0.5 transition-all shadow-sm hover:shadow-md">
                        <i class="fas fa-check"></i> Activer le compte
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- User Information --}}
            <div class="info-card">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-user text-orange-500"></i>
                    Informations utilisateur
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="info-label">Nom complet</div>
                        <div class="info-value">{{ $user->name }}</div>
                    </div>
                    <div>
                        <div class="info-label">Email</div>
                        <div class="info-value">{{ $user->email }}</div>
                    </div>
                    <div>
                        <div class="info-label">Rôle</div>
                        <div class="info-value">{{ $user->getRoleLabelAttribute() }}</div>
                    </div>
                    <div>
                        <div class="info-label">Statut du compte</div>
                        <div class="info-value">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $user->is_active ? 'Activé' : 'Inactif' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Email vérifié</div>
                        <div class="info-value">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $user->hasVerifiedEmail() ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ $user->hasVerifiedEmail() ? 'Oui' : 'Non' }}
                            </span>
                        </div>
                    </div>
                    <div>
                        <div class="info-label">Date d'inscription</div>
                        <div class="info-value">{{ $user->created_at->format('d/m/Y H:i') }}</div>
                    </div>
                </div>
            </div>

            {{-- Company Information --}}
            @if($user->company)
            <div class="info-card">
                <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                    <i class="fas fa-building text-orange-500"></i>
                    Informations société
                </h3>
                <div class="space-y-4">
                    <div>
                        <div class="info-label">Nom de la société</div>
                        <div class="info-value">{{ $user->company->name }}</div>
                    </div>
                    <div>
                        <div class="info-label">Nom commercial</div>
                        <div class="info-value">{{ $user->company->commercial_name ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Email société</div>
                        <div class="info-value">{{ $user->company->email }}</div>
                    </div>
                    <div>
                        <div class="info-label">Téléphone</div>
                        <div class="info-value">{{ $user->company->phone }}</div>
                    </div>
                    <div>
                        <div class="info-label">SIRET</div>
                        <div class="info-value">{{ $user->company->siret }}</div>
                    </div>
                    <div>
                        <div class="info-label">TVA</div>
                        <div class="info-value">{{ $user->company->tva }}</div>
                    </div>
                    <div>
                        <div class="info-label">Type de garage</div>
                        <div class="info-value">{{ $user->company->garage_type ?? 'N/A' }}</div>
                    </div>
                    <div>
                        <div class="info-label">Connu par</div>
                        <div class="info-value">{{ $user->company->known_by ?? 'N/A' }}</div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-check"></i> {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="fixed top-4 right-4 bg-red-500 text-white px-6 py-3 rounded-lg shadow-lg z-50">
            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
        </div>
    @endif
</body>
@endsection
