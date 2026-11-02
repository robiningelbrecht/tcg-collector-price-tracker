import Chart from 'chart.js/auto';
import {initBinderLightbox} from './features/binders/lightbox';
import {initBinderUpload} from './features/binders/upload';
import {initCardModal} from './features/cards/card-modal';
import {initCharts} from './features/prices/charts';
import {initDataTables} from './components/data-table';
import {initDispatchCommandForms} from './components/dispatch-command-form';
import {initModals} from './components/modal';
import {initPriceToggle} from './features/prices/price-visibility';
import {initSetSearch} from './features/cards/set-search';
import {initSparklines} from './features/prices/sparklines';
import {initTabs} from './components/tabs';

const cssVar = (name) => getComputedStyle(document.documentElement).getPropertyValue(name).trim();

Chart.defaults.font.family = getComputedStyle(document.body).fontFamily;
Chart.defaults.color = cssVar('--color-muted');

initCharts(document);
initSparklines(document);
initDataTables(document);
initDispatchCommandForms(document);
initModals(document);
initPriceToggle(document);
initTabs(document);
initBinderUpload(document);
document.querySelectorAll('[data-binder-lightbox]').forEach(initBinderLightbox);
document.querySelectorAll('[data-card-modal]').forEach(initCardModal);
document.querySelectorAll('[data-set-search]').forEach(initSetSearch);
