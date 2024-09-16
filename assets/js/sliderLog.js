import Swiper from 'swiper/bundle';

// import styles bundle
import 'swiper/css/bundle';
const swiper = new Swiper('.swiper', {
    // Optional parameters
    direction: 'horizontal',
    loop: true,
    speed:700,
    effect: "fade",
  
  
    // Navigation arrows
    navigation: {
      nextEl: '.arrow_next_quotes',
      prevEl: '.arrow_prev_quotes',
    },
    breakpoints: {
    
        1024: {
            slidesPerView: 1,
 
        }
    
    },
    autoplay:true,
    autoplay: {
        delay: 2000, // Durée entre chaque changement de slide en millisecondes
        disableOnInteraction: true, // Empêche l'autoplay de s'arrêter lors d'une interaction utilisateur (par défaut true)
    }
  
    // And if we need scrollbar
  
  });
  


// Fonction pour ouvrir le trailer
