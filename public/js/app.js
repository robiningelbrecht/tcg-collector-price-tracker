import Chart from 'chart.js/auto';
import {initCardModal} from './card-modal';
import {initCharts} from './charts';
import {initDataTables} from './data-table';
import {initDispatchCommandForms} from './dispatch-command-form';
import {initSparklines} from './sparklines';

const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.color = cssVar('--color-muted');

initCharts(document);
initSparklines(document);
initDataTables(document);
initDispatchCommandForms(document);
document.querySelectorAll('[data-card-modal]').forEach(initCardModal);
