<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <div class="text-base mb-1 font-semibold ">
        <a class="text-label text-stone-600 dark:text-slate-400">Alert status by region</a>
    </div>

    <div class="overflow-y-auto w-full">
        <table class="w-full border-collapse border-b border-stone-300 dark:border-slate-700">
          <thead class="text-xs font-semibold">
            <tr class="text-left">
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Status</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Bali & Nusa Tenggara</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Java</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Kalimantan</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Maluku</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Papua</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Sulawesi</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">Sumatra</th>
              <th class="border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">TOTAL</th>
            </tr>
          </thead>
          <tbody class="text-label text-stone-500 dark:text-slate-400">
            @foreach ($alerts as $item )
                <tr class="border-t border-stone-200 dark:border-slate-700 hover:bg-stone-50 dark:hover:bg-slate-800 transition-none">
                    <td class="
                    @if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['auditorStatus']}}
                    </td>


                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Balinusatenggara']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Java']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Kalimantan']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Maluku']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Papua']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Sulawesi']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['Sumatra']}}</td>
                    <td class="@if($item['auditorStatus'] === 'Grand Total') bg-stone-100 dark:bg-slate-800 dark:bg-slate-700 dark:text-slate-400
                    @elseif($item['auditorStatus'] === 'approved') bg-green-alerta-table
                    @elseif($item['auditorStatus'] === 'rejected' or $item['auditorStatus'] === 'duplicate') bg-merah-alerta-table
                    @elseif($item['auditorStatus'] === 'pre-approved') bg-gray-alerta-table
                    @elseif($item['auditorStatus'] === 'refined') bg-refined-alerta-table
                    @elseif($item['auditorStatus'] === 'reexportimage' or $item['auditorStatus'] === 'reclassification') bg-yellow-alerta-table
                    @endif border-b border-stone-300 dark:border-slate-700 dark:border-slate-700 px-4 py-2">{{$item['TOTAL']}}</td>
                </tr>
            @endforeach


          </tbody>
        </table>
      </div>



</div>
