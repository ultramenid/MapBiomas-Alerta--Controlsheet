import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';
import './work-trend-chart';
import { initTheme, toggleTheme } from './theme';

initTheme();

window.toggleTheme = toggleTheme;


