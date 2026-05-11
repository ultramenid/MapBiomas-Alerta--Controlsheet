import './bootstrap';
import '../../vendor/masmerise/livewire-toaster/resources/js';
import { initTheme, toggleTheme } from './theme';

initTheme();

window.toggleTheme = toggleTheme;


