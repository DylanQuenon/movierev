import Swiper, { Navigation, EffectFade, Autoplay } from 'swiper/bundle';
import 'swiper/css/bundle';

const swiper = new Swiper('.swiper', {
    direction: 'horizontal',
    loop: true,

    speed: 1200,
   

    autoplay: {
        delay: 5000,
        disableOnInteraction: true, // Désactiver l'autoplay sur interaction
    },
    breakpoints: {
        767: {
            slidesPerView: 1,
        },
        1024: {
            slidesPerView: 3,
        }
    },
    navigation: {
        nextEl: '.swiper-button-next',
        prevEl: '.swiper-button-prev',
    }

});

// Fonction pour activer ou désactiver l'autoplay
export function setAutoplay(enabled) {
    if (enabled) {
        swiper.autoplay.start();
    } else {
        swiper.autoplay.stop();
    }
}

// Ajouter des gestionnaires d'événements pour les diapositives
document.querySelectorAll('.swiper-slide').forEach(slide => {
    slide.addEventListener('mouseenter', () => {
        setAutoplay(false); // Désactiver l'autoplay au survol
    });
    slide.addEventListener('mouseleave', () => {
        setAutoplay(true); // Réactiver l'autoplay lorsqu'on quitte le survol
    });
});



// Fonction pour ouvrir le trailer
