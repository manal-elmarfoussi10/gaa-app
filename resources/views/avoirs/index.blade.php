@extends('layout')

@section('content')
<div class="p-4 sm:p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-6 p-6 bg-white rounded-xl shadow-sm border border-gray-100">
        <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Mes Avoirs</h1>
            <p class="text-gray-600 mt-1">Liste des avoirs enregistrés dans le système</p>
        </div>
        <a href="{{ route('avoirs.create') }}" class="flex items-center gap-2 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white px-4 py-2.5 rounded-lg transition-all shadow-md hover:shadow-lg">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd" />
            </svg>
            Ajouter un Avoir
        </a>
    </div>

    <!-- Control panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <!-- Column visibility -->
            <div class="relative">
                <button onclick="toggleAvoirColumnMenu()" class="flex items-center gap-2 border border-gray-200 bg-white px-4 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Colonnes visibles
                </button>
                <div id="avoirColumnMenu" class="absolute left-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-50 p-4 hidden">
                    <h3 class="font-medium text-gray-800 mb-3">Afficher/Masquer</h3>
                    <ul class="space-y-2">
                        @foreach ([
                            'col-date'    => 'Date',
                            'col-dossier' => 'Dossier',
                            'col-actions' => 'Actions',
                            'col-avoir'   => 'Avoir',
                            'col-ht'      => 'HT',
                            'col-ttc'     => 'TTC',
                            'col-facture' => 'Facture associé',
                            'col-annee'   => 'Année fiscale',
                            'col-rdv'     => 'Date de RDV',
                        ] as $column => $label)
                        <li>
                            <label class="flex items-center gap-2 text-sm cursor-pointer text-gray-700">
                                <input type="checkbox" class="column-toggle-avoir rounded text-orange-500" data-column="{{ $column }}" checked>
                                <span>{{ $label }}</span>
                            </label>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <!-- Export buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('avoirs.export.excel') }}" class="flex items-center gap-2 border border-gray-200 px-4 py-2.5 rounded-lg text-sm bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Export Excel
                </a>
                <a href="{{ route('avoirs.export.pdf') }}" class="flex items-center gap-2 border border-gray-200 px-4 py-2.5 rounded-lg text-sm bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full table-auto min-w-[1200px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-date">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-dossier">Dossier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-actions">Actions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-avoir">Avoir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-ht">HT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-ttc">TTC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-facture">Facture associé</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-annee">Année fiscale</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-rdv">Date de RDV</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($avoirs as $avoir)
                    @php
                        $client = $avoir->facture?->client;
                        $rdv    = $client?->rdvs?->first();
                    @endphp
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 col-date">
                            {{ optional($avoir->created_at)->format('d/m/Y') }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 col-dossier">
                            {{ $client?->nom_assure ?? '-' }}
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 col-actions">
                            <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                <!-- Voir (preview modal) -->
                                <a href="#"
                                   class="inline-flex items-center gap-1 text-sky-700 hover:text-sky-900"
                                   data-url="{{ route('avoirs.preview', $avoir->id) }}"
                                   onclick="openAvoirPreview(this); return false;">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24" fill="currentColor">
                                        <path d="M10 12a2 2 0 114 0 2 2 0 01-4 0z" />
                                        <path fill-rule="evenodd" d="M12 5c-7 0-10 7-10 7s3 7 10 7 10-7 10-7-3-7-10-7z" clip-rule="evenodd"/>
                                    </svg>
                                    Voir
                                </a>

                                <a href="{{ route('avoirs.edit', $avoir->id) }}" class="flex items-center gap-1 text-blue-600 hover:text-blue-800">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                    </svg>
                                    Modifier
                                </a>

                                <form action="{{ route('avoirs.destroy', $avoir->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="flex items-center gap-1 text-red-600 hover:text-red-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
                                        </svg>
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm col-avoir">
                            <a href="{{ route('avoirs.pdf', $avoir->id) }}" class="inline-flex items-center gap-1 bg-teal-100 hover:bg-teal-200 text-teal-700 px-2 py-1 rounded">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M3 3a2 2 0 012-2h6l6 6v10a2 2 0 01-2 2H5a2 2 0 01-2-2V3zm8 1.5V8h3.5L11 4.5z" clip-rule="evenodd"/>
                                </svg>
                                Télécharger
                            </a>
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm col-ht">{{ number_format((float)$avoir->montant, 2, ',', ' ') }} €</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm col-ttc">{{ number_format((float)$avoir->montant, 2, ',', ' ') }} €</td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm col-facture">
                            @if($avoir->facture)
                                <a href="{{ route('factures.show', $avoir->facture->id) }}" class="text-blue-600 hover:underline">
                                    {{ $avoir->facture->numero ?? ('Facture #'.$avoir->facture->id) }}
                                </a>
                            @else
                                -
                            @endif
                        </td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm col-annee">{{ optional($avoir->created_at)->year }}</td>

                        <td class="px-6 py-4 whitespace-nowrap text-sm col-rdv">
                            {{ $rdv?->start_time?->format('d/m/Y H:i') ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div id="avoirPreviewModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-[100] hidden">
    <div class="absolute inset-0 flex items-center justify-center p-4">
        <div class="bg-white w-full max-w-5xl h-[80vh] rounded-xl shadow-2xl border border-gray-200 overflow-hidden relative">
            <div class="flex items-center justify-between px-4 py-3 border-b">
                <h3 class="text-sm font-semibold text-gray-700">Aperçu de l’avoir</h3>
                <button onclick="closeAvoirPreview()" class="p-2 rounded hover:bg-gray-100" aria-label="Fermer">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 11-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 11-1.414-1.414L8.586 10l-4.95-4.95A1 1 0 115.05 3.636L10 8.586z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
            <iframe id="avoirPreviewFrame" src="about:blank" class="w-full h-[calc(80vh-48px)]"></iframe>
        </div>
    </div>
</div>

<script>
    // Column menu
    function toggleAvoirColumnMenu() {
        document.getElementById('avoirColumnMenu').classList.toggle('hidden');
    }

    document.addEventListener('click', function(e) {
        const menu = document.getElementById('avoirColumnMenu');
        const btn  = document.querySelector('button[onclick="toggleAvoirColumnMenu()"]');
        if (!menu.contains(e.target) && !btn.contains(e.target)) { menu.classList.add('hidden'); }
    });

    // Column visibility persistence
    document.querySelectorAll('.column-toggle-avoir').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const cls = this.dataset.column;
            const visible = this.checked;
            document.querySelectorAll('.' + cls).forEach(td => td.style.display = visible ? '' : 'none');
            localStorage.setItem(cls, visible);
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.column-toggle-avoir').forEach(toggle => {
            const cls = toggle.dataset.column;
            const visible = localStorage.getItem(cls) !== 'false';
            toggle.checked = visible;
            document.querySelectorAll('.' + cls).forEach(td => td.style.display = visible ? '' : 'none');
        });
    });

    // Preview modal
    function openAvoirPreview(el) {
        const url = el.getAttribute('data-url');
        const frame = document.getElementById('avoirPreviewFrame');
        frame.src = url;
        document.getElementById('avoirPreviewModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeAvoirPreview() {
        const m = document.getElementById('avoirPreviewModal');
        const frame = document.getElementById('avoirPreviewFrame');
        frame.src = 'about:blank';
        m.classList.add('hidden');
        document.body.style.overflow = '';
    }
</script>
@endsection