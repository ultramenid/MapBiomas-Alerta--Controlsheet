<div class="py-6 px-4 border border-stone-200 dark:border-slate-700 z-20 relative  bg-stone-50 dark:bg-slate-800 dark:bg-slate-800 dark:border-slate-800 mt-4">
    <div class="text-sm mb-6">
        <a class="text-label text-stone-600 dark:text-slate-400 mb-1">Alert by Validator</a>
        <div class="w-full mt-1 flex gap-2" wire:ignore x-init="
        flatpickr('#rangeValidator', {
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

                    let startDateValidator = formatDate(selectedDates[0]);
                    let endDateValidator   = formatDate(selectedDates[1]);

                    console.log(['Start:', startDateValidator, 'End:', endDateValidator]);

                    $wire.set('startDateValidator', startDateValidator);
                    $wire.set('endDateValidator', endDateValidator);
                }
            }
        });
     "
        ">
            <input id="rangeValidator" type="text" class="bg-white dark:bg-slate-900 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 w-52 rounded-sm px-3 py-2 text-sm focus:outline-none transition-none"  wire:model.defer='rangeValidator' placeholder="Please select">

        </div>
    </div>

    <div>


    <div class="overflow-hidden">
        <div class="overflow-x-auto">

            <table class="w-full min-w-max border-collapse border-b border-stone-300 dark:border-slate-700 dark:border-slate-800 text-xs">

                <thead class="text-xs">

                    <!-- HEADER ROW 1 -->
                    <tr>

                        <!-- Validator -->
                        <th rowspan="2 "
                            class="sticky left-0 bg-stone-100 dark:bg-slate-800 text-left px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10 border-b border-stone-200 dark:border-slate-700">
                            Validator
                        </th>

                        <!-- Loop tanggal -->
                        @foreach($report['dates'] as $date)
                            <th colspan="2"
                                class=" px-4 py-2 text-center whitespace-nowrap bg-stone-200 dark:bg-slate-700 dark:bg-slate-600 dark:text-slate-400 dark:border-slate-500 border-r border-stone-300 dark:border-slate-700 border-l border-b border-t">
                                {{ $date }}
                            </th>
                        @endforeach

                        <!-- Total -->
                        <th colspan="2"
                            class="sticky right-0 bg-stone-100 dark:bg-slate-800 text-center px-3 py-2.5 text-label text-stone-500 dark:text-slate-400 z-10 border-b border-stone-200 dark:border-slate-700">
                            Total
                        </th>

                    </tr>

                    <!-- HEADER ROW 2 -->
                    <tr>

                        @foreach($report['dates'] as $date)
                            <th class="w-24 bg-stone-200 dark:bg-slate-700 dark:bg-slate-500 dark:text-slate-300 dark:border-slate-500 border-stone-300 dark:border-slate-700 px-4 py-2 text-center border-l">
                                task
                            </th>

                            <th class="w-24  border-stone-300 dark:border-slate-700 px-4 py-2 text-center bg-[#bfcec3] dark:bg-[#617c6a] dark:text-slate-300 dark:border-slate-500 border-r">
                                approved
                            </th>
                        @endforeach

                        <th colspan="1" class="sticky right-[112px] bg-stone-100 dark:bg-slate-800 text-center px-3 py-2.5 text-stone-700 dark:text-slate-300 z-10 border-b border-stone-200 dark:border-slate-700">
                            task
                        </th>

                        <th colspan="1" class="w-28  sticky right-0 bg-[#a3c9af] border-b border-[#a3c9af] px-4 py-2 text-center z-20  dark:bg-[#3a5142]  dark:text-slate-300 dark:border-[#3a5142] border-r">
                            approved
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @foreach($report['data'] as $row)

                        <tbody x-data="{ open:false }">

                            <tr @click="open = !open"
                                class=" cursor-pointer">

                                <!-- Validator name (sticky left, collapse tetap) -->
                                <td class="sticky left-0 bg-white dark:bg-slate-900 px-3 py-2.5 z-10 whitespace-nowrap font-medium border-b border-stone-200 dark:border-slate-700">

                                    <div class="flex items-center gap-2">

                                        {{ $row['validatorName'] }}

                                        <svg class="w-3 h-3 text-stone-400 dark:text-slate-500 transition-none"
                                            :class="{ 'rotate-90': open }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24">

                                            <path stroke-linecap="round"
                                                stroke-linejoin="round"
                                                stroke-width="2"
                                                d="M9 5l7 7-7 7" />
                                        </svg>

                                    </div>

                                    <!-- collapse content tetap -->
                                    <div x-show="open" style="display: none;" class="mt-2 w-full bg-stone-100 dark:bg-slate-700 dark:text-slate-300 px-3 py-2 rounded-sm">

                                        Insert: {{ $row['category']['Insert'] ?? 0 }} <br>
                                        Reject: {{ $row['category']['Reject'] ?? 0 }} <br>
                                        Reclassification: {{ $row['category']['reclassification'] ?? 0 }} <br>
                                        Reexport Image: {{ $row['category']['reexportimage'] ?? 0 }} <br>
                                        Refined: {{ $row['category']['refined'] ?? 0 }} <br>
                                        Approved: {{ $row['category']['approved'] ?? 0 }}

                                    </div>

                                </td>


                                <!-- Loop tanggal -->
                                @foreach($report['dates'] as $date)

                                    <td class="border-b border-stone-300 dark:border-slate-700 px-4 py-2 text-center bg-stone-200 dark:bg-slate-700 dark:bg-slate-500 dark:text-slate-300 dark:border-slate-500 border-l">
                                        {{ $row['dates'][$date]['task'] ?? 0 }}

                                    </td>

                                    <td class="border-b border-stone-300 dark:border-slate-700 px-4 py-2 text-center bg-[#bfcec3] dark:bg-[#617c6a] dark:text-slate-300 dark:border-slate-500 border-r ">
                                        {{ $row['dates'][$date]['approved'] ?? 0 }}
                                    </td>

                                @endforeach


                                <!-- total task -->
                                <td class="w-28 sticky right-[112px] bg-stone-300 dark:bg-slate-600 border-b border-stone-300 dark:border-slate-700 px-4 py-2 text-center font-semibold z-10 dark:bg-slate-600 dark:text-slate-300 dark:border-slate-600">
                                    {{ $row['grandTotal'] ?? 0 }}
                                </td>

                                <!-- total approved -->
                                <td class="w-28 sticky right-0  border-b border-[#a1ddb5] px-4 py-2 text-center font-semibold z-10 bg-[#a3c9af]   dark:bg-[#3a5142]  dark:text-slate-300 dark:border-[#3a5142] border-r">
                                    {{ $row['grandApproved'] ?? 0 }}
                                </td>

                            </tr>

                        </tbody>

                    @endforeach

                </tbody>

            </table>



        </div>
    </div>


</div>



</div>
