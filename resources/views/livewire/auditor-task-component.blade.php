<div class="glass rounded-sm p-5 mb-5 z-20 relative dark:text-slate-400">
    <div class="text-sm mb-6">
        <a class="text-label text-stone-600 dark:text-slate-400 mb-1">Alert by Auditor</a>
        <div class="w-full mt-1 flex gap-2" wire:ignore x-init="
        flatpickr('#rangeAuditor', {
            mode:'range',
            dateFormat: 'Y-m-d',
            {{-- locale: 'id', // ✅ Indonesian calendar labels, optional --}}
            onChange: function(selectedDates) {
                if (selectedDates.length === 2) {
                    // Jakarta timezone formatter
                    let options = { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' };

                    function formatDate(d) {
                        let parts = new Intl.DateTimeFormat('id-ID', options).formatToParts(d);
                        let y = parts.find(p => p.type === 'year').value;
                        let m = parts.find(p => p.type === 'month').value;
                        let day = parts.find(p => p.type === 'day').value;
                        return `${y}-${m}-${day}`;
                    }

                    let startDate = formatDate(selectedDates[0]);
                    let endDate   = formatDate(selectedDates[1]);

                    console.log(['Start:', startDate, 'End:', endDate]);

                    $wire.set('startDate', startDate);
                    $wire.set('endDate', endDate);
                }
            }
        });
     "
        ">
            <input id="rangeAuditor" type="text" class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='rangeAuditor' placeholder="Please select">

        </div>
    </div>

    <div class="max-w-7xl mx-auto">
        <div class="">
            <div class="overflow-x-auto">
    <table class="w-full border-collapse">
        <thead class="text-xs">
            <tr>
                {{-- Sticky first column --}}
                <th class="sticky left-0 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10 border-b border-stone-200 dark:border-slate-700">
                    Auditor
                </th>

                {{-- Loop dynamic date columns --}}
                @if (!empty($results))
                    @foreach (array_keys($results[array_key_first($results)]) as $key)
                        @if ($key !== 'auditorName' and $key !== 'auditorId')
                            <th class="border-b border-stone-300 dark:border-slate-700 px-4 py-2 text-xs text-center whitespace-nowrap">
                                {{ $key }}
                            </th>
                        @endif
                    @endforeach
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $row)
                <tr class="hover:bg-stone-50 dark:hover:bg-slate-800 transition-none">
                    {{-- Sticky first column --}}
                    <td class="sticky left-0 bg-white dark:bg-slate-900 px-3 py-2.5 z-10 whitespace-nowrap border-b border-stone-200 dark:border-slate-700">
                        <a href="{{ url('/auditor-alert/'.$row['auditorId']) }}">{{ $row['auditorName'] }}</a>
                    </td>

                    {{-- Show counts per date --}}
                    @foreach ($row as $key => $val)
                        @if ($key !== 'auditorName' and $key !== 'auditorId')
                            {{-- Display 0 if no data for that date --}}
                            <td class="border-b dark:bg-slate-700 border-b border-stone-300 dark:border-slate-700 dark:border-slate-800 border-stone-300 dark:border-slate-700 px-4 py-2 text-xs text-center">
                                {{ $val }}
                            </td>
                        @endif
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>



        </div>
    </div>

</div>
