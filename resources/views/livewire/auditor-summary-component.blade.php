<div class="glass rounded-sm p-5 mb-5">
    <div class="flex flex-col sm:flex-row sm:gap-6 gap-3 mb-5 items-start">
        <div>
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Alert by Auditor</div>
            <div wire:ignore x-init="
                flatpickr('#rangeAuditor', {
                    mode:'range',
                    dateFormat: 'Y-m-d',
                    onChange: function(selectedDates) {
                        if (selectedDates.length === 2) {
                            let options = { timeZone: 'Asia/Jakarta', year: 'numeric', month: '2-digit', day: '2-digit' };
                            function formatDate(d) {
                                let parts = new Intl.DateTimeFormat('id-ID', options).formatToParts(d);
                                let y = parts.find(p => p.type === 'year').value;
                                let m = parts.find(p => p.type === 'month').value;
                                let day = parts.find(p => p.type === 'day').value;
                                return `${y}-${m}-${day}`;
                            }
                            let startDate = formatDate(selectedDates[0]);
                            let endDate = formatDate(selectedDates[1]);
                            $wire.set('startDate', startDate);
                            $wire.set('endDate', endDate);
                            $wire.call('filter');
                        }
                    }
                });
            ">
                <input 
                    id="rangeAuditor" 
                    type="text" 
                    class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" 
                    wire:model.defer='rangeAuditor' 
                    placeholder="Select date range"
                >
            </div>
        </div>
        
        <div>
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Find Auditor</div>
            <input 
                wire:keydown.enter="find" 
                type="text" 
                class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" 
                wire:model.defer='alertCode' 
                placeholder="Type alert ID"
            >
        </div>
        
        <div>
            <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Find Validator</div>
            <input 
                wire:keydown.enter="findValidator" 
                type="text" 
                class="bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none" 
                wire:model.defer='alertCodeValidator' 
                placeholder="Type alert ID"
            >
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full border-collapse">
            <thead>
                <tr class="border-b border-stone-300 dark:border-slate-700">
                    <th class="sticky left-0 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10">Auditor</th>
                    @if (!empty($results))
                        @foreach (array_keys($results[array_key_first($results)]) as $key)
                            @if ($key !== 'auditorName' && $key !== 'auditorId' && $key !== 'Total')
                                <th class="text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 cursor-pointer" wire:click="sortBy('{{ $key }}')">
                                    {{ $key }}
                                    @if ($dataField === $key)
                                        <span>{{ $dataOrder === 'asc' ? '▲' : '▼' }}</span>
                                    @endif
                                </th>
                            @endif
                        @endforeach
                        <th class="sticky right-0 bg-stone-100 dark:bg-slate-800 text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10 cursor-pointer" wire:click="sortBy('Total')">
                            Total
                            @if ($dataField === 'Total')
                                <span>{{ $dataOrder === 'asc' ? '▲' : '▼' }}</span>
                            @endif
                        </th>
                    @endif
                </tr>
            </thead>
            <tbody class="text-sm">
                @foreach ($results as $row)
                    <tr class="border-b border-stone-200 dark:border-slate-800">
                        <td class="sticky left-0 bg-white dark:bg-slate-900 px-3 py-2.5 z-10">
                            <a href="{{ url('/auditor-alert/'.$row['auditorId']) }}" class="text-green-700 dark:text-green-400 hover:underline transition-none">
                                {{ $row['auditorName'] }}
                            </a>
                        </td>
                        @foreach ($row as $key => $val)
                            @if ($key !== 'auditorName' && $key !== 'auditorId' && $key !== 'Total')
                                <td class="text-center px-3 py-2.5 text-stone-700 dark:text-slate-300">{{ $val }}</td>
                            @endif
                        @endforeach
                        <td class="sticky right-0 bg-white dark:bg-slate-900 text-center px-3 py-2.5 font-bold text-stone-900 dark:text-slate-200 z-10">{{ $row['Total'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
