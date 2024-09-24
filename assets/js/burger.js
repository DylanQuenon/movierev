/****MENU BURGER****/
const burger = document.querySelector("#burger");
const menuMobile = document.querySelector("#menuMobile");
const linksMenu = document.querySelectorAll('#menuMobile nav ul li a');

const toggleMenu = () => {
    burger.classList.toggle('open-burger');
    menuMobile.classList.toggle('open-menu');

    // Ajouter ou retirer la classe no-scroll sur le body
    document.body.classList.toggle('no-scroll', menuMobile.classList.contains('open-menu'));
};

// Écouter les clics sur le burger et les liens du menu
burger.addEventListener('click', toggleMenu);
linksMenu.forEach(link => {
    link.addEventListener('click', toggleMenu);
});

// Ajout des classes 'hide' et 'show' sur le header en fonction du scroll
const header = document.querySelector('header');
let lastScrollValue = 0;

document.addEventListener('scroll', () => {
    let top = document.documentElement.scrollTop || document.body.scrollTop || 0;
    if (lastScrollValue < top) {
        header.classList.add('hide');
        burger.classList.add('hide');
        header.classList.remove('show');
        burger.classList.remove('show');
    } else {
        header.classList.remove('hide');
        burger.classList.remove('hide');
        header.classList.add('show');
        burger.classList.add('show');
    }
    lastScrollValue = top;
});

// Fermer le menu burger si la fenêtre est redimensionnée
window.addEventListener('resize', () => {
    if (menuMobile.classList.contains('open-menu')) {
        toggleMenu(); // Ferme le menu si il est ouvert
    }
});
