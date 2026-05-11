<div class="glass rounded-sm p-4 mb-5">
    <div class="text-label text-stone-600 dark:text-slate-400 mb-3">Filter</div>
    <div class="flex gap-3">
        <div class="sm:w-36 w-full relative">
            <select 
                wire:ignore 
                id='date-dropdown' 
                wire:model.live="yearAlert" 
                class="w-full appearance-none bg-white dark:bg-slate-800 border border-stone-300 dark:border-slate-600 text-stone-900 dark:text-slate-100 px-3 py-2 text-sm rounded-sm focus:outline-none cursor-pointer transition-none"
            >
                <option value="all">All Years</option>
            </select>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="absolute pointer-events-none right-3 top-2.5 size-4 text-stone-500">
                <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
            </svg>
        </div>
        <button 
            wire:click='filter' 
            class="bg-stone-900 dark:bg-slate-200 text-white dark:text-stone-900 py-2 px-4 text-sm font-semibold rounded-sm cursor-pointer hover:bg-stone-800 dark:hover:bg-slate-300 transition-none"
        >
            Apply
        </button>
    </div>

    <script>
        let dateDropdown = document.getElementById('date-dropdown');
        let currentYear = new Date().getFullYear();
        let earliestYear = 2020;
        while (currentYear >= earliestYear) {
            let dateOption = document.createElement('option');
            dateOption.text = currentYear;
            dateOption.value = currentYear;
            dateDropdown.add(dateOption);
            currentYear -= 1;
        }
    </script>
</div>
