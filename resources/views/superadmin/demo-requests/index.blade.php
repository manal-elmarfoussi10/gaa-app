@extends('layout')

@section('content')
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demandes de démo — Super Admin</title>
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
        .custom-stat-card { transition: transform .3s ease, box-shadow .3s ease; border-left: 4px solid var(--primary); position: relative; overflow: hidden; cursor:pointer; }
        .custom-stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -2px rgba(0,0,0,.05); }
        .custom-stat-card::before { content:''; position:absolute; top:0; right:0; width:80px; height:80px; background:var(--primary-extra-light); border-radius:0 0 0 100%; z-index:0; transition: all .4s ease; }
        .custom-stat-card:hover::before { width:100%; height:100%; border-radius:1rem; }
        .custom-stat-icon { width:48px; height:48px; background:var(--primary-extra-light); border-radius:12px; display:flex; align-items:center; justify-content:center; color:var(--primary); font-size:20px; transition: all .3s ease; z-index:1; }
        .custom-stat-card:hover .custom-stat-icon { transform: scale(1.1); background:var(--primary); color:#fff; }
        .custom-stat-card:hover .stat-value { color:var(--primary); }
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
                Demandes de <span class="text-orange-500">Démo</span>
            </h1>
            <div class="flex gap-4">
                <a href="{{ route('superadmin.dashboard') }}" class="flex items-center gap-2 px-4 py-2 rounded-lg font-semibold border border-gray-300 text-gray-700 hover:bg-gray-100 hover:border-gray-500 transition-all shadow-sm">
                    <i class="fas fa-arrow-left"></i> Retour au tableau de bord
                </a>
            </div>
        </div>

        {{-- Stats Card --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="custom-stat-card bg-white rounded-xl p-6 shadow-md">
                <div class="flex justify-between items-center mb-4 relative">
                    <div>
                        <div class="text-sm font-semibold text-gray-500 uppercase">Demandes en attente</div>
                        <div class="text-2xl font-bold text-gray-800 stat-value">{{ $demoRequests->total() }}</div>
                        <div class="text-gray-500 text-sm">Utilisateurs inactifs</div>
                    </div>
                    <div class="custom-stat-icon"><i class="fas fa-clock"></i></div>
                </div>
            </div>
        </div>

        {{-- Demo Requests Table --}}
        <div class="bg-white rounded-xl p-6 shadow-md transition-transform hover:-translate-y-1">
            <div class="flex justify-between items-center mb-5">
                <h2 class="text-lg font-bold text-gray-800">Demandes de démonstration</h2>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr>
                            <th class="text-left px-5 py-4 font-semibold text-gray-700 uppercase text-xs tracking-wider border-b border-gray-300 bg-white sticky top-0">Utilisateur</th>
                            <th class="text-left px-5 py-4 font-semibold text-gray-700 uppercase text-xs tracking-wider border-b border-gray-300 bg-white sticky top-0">Société</th>
                            <th class="text-left px-5 py-4 font-semibold text-gray-700 uppercase text-xs tracking-wider border-b border-gray-300 bg-white sticky top-0">Email</th>
                            <th class="text-left px-5 py-4 font-semibold text-gray-700 uppercase text-xs tracking-wider border-b border-gray-300 bg-white sticky top-0">Téléphone</th>
                            <th class="text-left px-5 py-4 font-semibold text-gray-700 uppercase text-xs tracking-wider border-b border-gray-300 bg-white sticky top-0">Date d'inscription</th>
                            <th class="text-left px-5 py-4 font-semibold text-gray-700 uppercase text-xs tracking-wider border-b border-gray-300 bg-white sticky top-0">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($demoRequests as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-5 py-4 border-b border-gray-200 text-gray-700">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                            <i class="fas fa-user text-orange-600"></i>
                                        </div>
                                        <div>
                                            <div class="font-medium">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->role }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 text-gray-700">
                                    {{ $user->company->name ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 text-gray-700">
                                    {{ $user->email }}
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 text-gray-700">
                                    {{ $user->company->phone ?? 'N/A' }}
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200 text-gray-700">
                                    {{ $user->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-5 py-4 border-b border-gray-200">
                                    <div class="flex gap-2">
                                        <a href="{{ route('superadmin.demo-requests.show', $user->id) }}"
                                           class="px-3 py-1 bg-blue-500 text-white text-sm rounded hover:bg-blue-600 transition-colors">
                                            <i class="fas fa-eye"></i> Voir
                                        </a>
                                        <form method="POST" action="{{ route('superadmin.demo-requests.activate', $user->id) }}"
                                              class="inline"
                                              onsubmit="return confirm('Êtes-vous sûr de vouloir activer ce compte ?')">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="px-3 py-1 bg-green-500 text-white text-sm rounded hover:bg-green-600 transition-colors">
                                                <i class="fas fa-check"></i> Activer
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-5 py-4 text-gray-400" colspan="6">Aucune demande de démonstration en attente.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($demoRequests->hasPages())
                <div class="mt-6">
                    {{ $demoRequests->links() }}
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
