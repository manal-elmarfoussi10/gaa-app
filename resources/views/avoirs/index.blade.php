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
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v2H7a1 1 0 100 2h2v2a1 1 0 102 0v-2h2a1 1 0 100-2h-2V7z" clip-rule="evenodd"/></svg>
            Ajouter un Avoir
        </a>
    </div>

    <!-- Control panel -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 mb-6">
        <div class="flex flex-wrap items-center gap-4">
            <!-- Column Visibility -->
            <div class="relative">
                <button onclick="toggleAvoirColumnMenu()" class="flex items-center gap-2 border border-gray-200 bg-white px-4 py-2.5 rounded-lg text-sm text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" clip-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                    Colonnes visibles
                </button>
                <div id="avoirColumnMenu" class="absolute left-0 mt-2 w-64 bg-white border border-gray-200 rounded-xl shadow-lg z-50 p-4 hidden">
                    <h3 class="font-medium text-gray-800 mb-3">Afficher/Masquer</h3>
                    <ul class="space-y-2">
                        @foreach([
                            'col-date'     => 'Date',
                            'col-dossier'  => 'Dossier',
                            'col-actions'  => 'Actions',
                            'col-avoir'    => 'Avoir',
                            'col-ht'       => 'HT',
                            'col-ttc'      => 'TTC',
                            'col-facture'  => 'Facture associée',
                            'col-annee'    => 'Année fiscale',
                            'col-rdv'      => 'Date de RDV',
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

            <!-- Export Buttons -->
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('avoirs.export.excel') }}" class="flex items-center gap-2 border border-gray-200 px-4 py-2.5 rounded-lg text-sm bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    Export Excel
                </a>
                <a href="{{ route('avoirs.export.pdf') }}" class="flex items-center gap-2 border border-gray-200 px-4 py-2.5 rounded-lg text-sm bg-white text-gray-700 hover:bg-gray-50">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-red-500" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                    Export PDF
                </a>
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-xl overflow-hidden border border-gray-100">
        <div class="overflow-x-auto">
            <table class="w-full table-auto min-w-[1100px]">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-date">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-dossier">Dossier</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-actions">Actions</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-avoir">Avoir</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-ht">HT</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-ttc">TTC</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-facture">Facture associée</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-annee">Année fiscale</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider col-rdv">Date de RDV</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($avoirs as $avoir)
                        @php
                            $facture = $avoir->facture;
                            // Client name OR prospect name (on facture) OR '-'
                            $displayName = $facture?->client?->nom_assure
                                ?? ($facture?->prospect_name ?: '-');

                            // Very defensive RDV extraction (will be '-' if any layer is missing)
                            $firstRdv = $facture?->client?->rdvs?->first();
                            $rdvText  = $firstRdv?->start_time ?? '-';
                        @endphp
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 col-date">
                                {{ $avoir->created_at?->format('d/m/Y') ?? '-' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 col-dossier">
                                {{ $displayName }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-actions">
                                <div class="flex flex-wrap items-center gap-3">
                                    <!-- Voir (preview AVOIR) -->
                                    <a href="#"
                                       class="inline-flex items-center gap-1 text-blue-600 hover:text-blue-800 voir-avoir"
                                       data-url="{{ route('avoirs.preview', $avoir->id) }}">
                                        <span class="inline-block w-2 h-2 rounded-full bg-blue-600"></span>
                                        Voir
                                    </a>

                                    <a href="{{ route('avoirs.edit', $avoir->id) }}" class="flex items-center gap-1 text-blue-600 hover:text-blue-800">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"/></svg>
                                        Modifier
                                    </a>

                                    <form action="{{ route('avoirs.destroy', $avoir->id) }}" method="POST" onsubmit="return confirm('Confirmer la suppression ?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="flex items-center gap-1 text-red-600 hover:text-red-800">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-avoir">
                                <a href="{{ route('avoirs.pdf', $avoir->id) }}"
                                   class="inline-flex items-center gap-2 bg-teal-100 text-teal-700 px-3 py-1 rounded">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                    Télécharger
                                </a>
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-ht">
                                {{ number_format((float) $avoir->montant, 2, ',', ' ') }} €
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-ttc">
                                {{ number_format((float) $avoir->montant, 2, ',', ' ') }} €
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-facture">
                                @if($facture)
                                    <a href="#"
                                       class="text-blue-600 hover:underline voir-facture"
                                       data-url="{{ route('factures.preview', $facture->id) }}">
                                        {{ $facture->numero ?? ('Facture #'.$facture->id) }}
                                    </a>
                                @else
                                    –
                                @endif
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-annee">
                                {{ $avoir->created_at?->year ?? '-' }}
                            </td>

                            <td class="px-6 py-4 whitespace-nowrap text-sm col-rdv">
                                {{ $rdvText }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center justify-center">
                                    <div class="bg-gray-100 p-5 rounded-full mb-4">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                        </svg>
                                    </div>
                                    <p class="mt-2 text-lg font-medium text-gray-700">Aucun avoir trouvé</p>
                                    <p class="mt-1 text-gray-500">Commencez par créer votre premier avoir</p>
                                    <a href="{{ route('avoirs.create') }}" class="mt-4 inline-flex items-center gap-2 px-4 py-2.5 bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg hover:from-orange-600 hover:to-orange-700 shadow-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/></svg>
                                        Créer un avoir
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Preview Modal (shared for avoir & facture) -->
<div id="previewModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50">
  <div class="bg-white w-[90vw] h-[85vh] max-w-5xl rounded-xl shadow-xl overflow-hidden">
    <div class="flex items-center justify-between px-4 py-2 border-b">
      <h3 class="font-semibold text-gray-800" id="previewTitle">Aperçu</h3>
      <button onclick="closePreview()" class="text-gray-500 hover:text-gray-700 text-2xl leading-none">&times;</button>
    </div>
    <iframe id="previewFrame" class="w-full h-[calc(85vh-44px)]" src=""></iframe>
  </div>
</div>

<script>
    // Column menu
    function toggleAvoirColumnMenu() {
        const menu = document.getElementById('avoirColumnMenu');
        menu.classList.toggle('hidden');
    }
    document.addEventListener('click', function(e) {
        const columnMenu = document.getElementById('avoirColumnMenu');
        const button = document.querySelector('button[onclick="toggleAvoirColumnMenu()"]');
        if (columnMenu && !columnMenu.contains(e.target) && !button.contains(e.target)) {
            columnMenu.classList.add('hidden');
        }
    });

    // Remember column visibility
    document.querySelectorAll('.column-toggle-avoir').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const columnClass = this.dataset.column;
            const isVisible = this.checked;
            document.querySelectorAll(`.${columnClass}`).forEach(cell => {
                cell.style.display = isVisible ? '' : 'none';
            });
            localStorage.setItem(`avoirs_${columnClass}`, isVisible ? '1' : '0');
        });
    });
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.column-toggle-avoir').forEach(toggle => {
            const columnClass = toggle.dataset.column;
            const isVisible = (localStorage.getItem(`avoirs_${columnClass}`) ?? '1') === '1';
            toggle.checked = isVisible;
            document.querySelectorAll(`.${columnClass}`).forEach(cell => {
                cell.style.display = isVisible ? '' : 'none';
            });
        });
    });

    // Preview helpers
    function openPreview(url, title = 'Aperçu') {
        const m = document.getElementById('previewModal');
        const f = document.getElementById('previewFrame');
        const t = document.getElementById('previewTitle');
        t.textContent = title;
        f.src = url;
        m.classList.remove('hidden');
        m.classList.add('flex');
    }
    function closePreview() {
        const m = document.getElementById('previewModal');
        const f = document.getElementById('previewFrame');
        f.src = 'about:blank';
        m.classList.add('hidden');
        m.classList.remove('flex');
    }
    document.getElementById('previewModal').addEventListener('click', function(e) {
        if (e.target === this) closePreview();
    });

    // Open AVOIR preview
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a.voir-avoir');
        if (a) {
            e.preventDefault();
            openPreview(a.dataset.url, "Aperçu de l’avoir");
        }
    });
    // Open FACTURE preview
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a.voir-facture');
        if (a) {
            e.preventDefault();
            openPreview(a.dataset.url, "Aperçu de la facture");
        }
    });
</script>
@endsection