<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <div class="text-label text-stone-600 dark:text-slate-400 mb-4">Alert Status by Region</div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-stone-300 dark:border-slate-700">
                    <th class="text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Status</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Bali & Nusa Tenggara</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Java</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Kalimantan</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Maluku</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Papua</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Sulawesi</th>
                    <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">Sumatra</th>
                    <th class="text-right px-3 py-2.5 text-label text-stone-500 dark:text-slate-400">TOTAL</th>
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($alerts as $item)
                    <tr class="border-b border-stone-200 dark:border-slate-800">
                        <td class="px-3 py-2.5 font-semibold 
                            @if($item['auditorStatus'] === 'Grand Total') text-stone-900 dark:text-slate-200 bg-stone-100 dark:bg-slate-800
                            @elseif($item['auditorStatus'] === 'approved') text-green-700 dark:text-green-400
                            @elseif($item['auditorStatus'] === 'rejected' || $item['auditorStatus'] === 'duplicate') text-red-700 dark:text-red-400
                            @elseif($item['auditorStatus'] === 'pre-approved') text-stone-600 dark:text-slate-400
                            @elseif($item['auditorStatus'] === 'refined') text-sky-700 dark:text-sky-400
                            @elseif($item['auditorStatus'] === 'reexportimage' || $item['auditorStatus'] === 'reclassification') text-amber-700 dark:text-amber-400
                            @endif">
                            {{$item['auditorStatus']}}
                        </td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Balinusatenggara']}}</td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Java']}}</td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Kalimantan']}}</td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Maluku']}}</td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Papua']}}</td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Sulawesi']}}</td>
                        <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{$item['Sumatra']}}</td>
                        <td class="text-right px-3 py-2.5 font-bold text-stone-900 dark:text-slate-200">{{$item['TOTAL']}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
