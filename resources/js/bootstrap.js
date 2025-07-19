import axios from 'axios';
import noUiSlider from 'nouislider';

window.noUiSlider = noUiSlider;
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

