import { Chart, registerables } from 'chart.js';
import { createIcons, icons } from 'lucide';
import { Html5Qrcode } from 'html5-qrcode';

// Register Chart.js modules
Chart.register(...registerables);
window.Chart = Chart;

// Expose Html5Qrcode
window.Html5Qrcode = Html5Qrcode;

// Initialize Lucide icons on DOM ready
document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});

// Helper for dynamic re-render of icons
window.refreshIcons = () => {
    createIcons({ icons });
};
