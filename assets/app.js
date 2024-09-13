import './bootstrap.js';
import { annotate } from 'rough-notation'; // Import RoughNotation
import AOS from 'aos';
import 'aos/dist/aos.css';


/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
*/

import './styles/app.scss';
import './js/home.js';
import tippy from 'tippy.js';
import 'tippy.js/dist/tippy.css';  // Styles par défaut


AOS.init();

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

document.addEventListener('DOMContentLoaded', function () {
    tippy('[data-tippy-content]', {
        // Options de configuration si besoin
        placement: 'top', // ou 'bottom', 'right', etc.

    
    });
});
