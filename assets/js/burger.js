/****MENU BURGER****/
const burger = document.querySelector("#burger");
const menuMobile = document.querySelector("#menuMobile");
const linksMenu = document.querySelectorAll('#menuMobile nav ul li a');

const toggleMenu = () => {
    burger.classList.toggle('open-burger');
    menuMobile.classList.toggle('open-menu');
};
linksMenu.forEach(link => {
    burger.addEventListener('click', toggleMenu);
    link.addEventListener('click', toggleMenu);
});