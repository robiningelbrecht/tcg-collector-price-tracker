import Chart from 'chart.js/auto';

const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.color = cssVar('--color-muted');
