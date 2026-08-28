import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';
import './work-trend-chart';
import './modal-components';
import { initTheme, toggleTheme } from './theme';

initTheme();

// wire:navigate swaps in a server-rendered <html> without the runtime-applied
// dark class — re-apply the stored theme after every navigation. The event
// fires synchronously after the swap, before the browser paints.
document.addEventListener('livewire:navigated', () => initTheme());

window.toggleTheme = toggleTheme;


