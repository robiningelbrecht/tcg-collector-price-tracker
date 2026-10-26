import Chart from 'chart.js/auto';
import {initDashboard} from './dashboard';

const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.color = cssVar('--color-muted');

const dashboardData = document.getElementById('dashboard-data');
if (dashboardData) {
    initDashboard(dashboardData);
}
