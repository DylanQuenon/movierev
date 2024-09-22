const addImage = document.querySelector('#add-castings');
addImage.addEventListener('click', () => {
    const widgetCounter = document.querySelector("#widgets-counter");
    const index = +widgetCounter.value;
    const annonceImages = document.querySelector("#media_castings") || document.querySelector("#media_edit_castings");

    const prototype = annonceImages.dataset.prototype.replace(/__name__/g, index);
    annonceImages.insertAdjacentHTML('beforeend', prototype);
    widgetCounter.value = index + 1;
    handleDeleteButtons(); // Pour mettre à jour la gestion des suppressions
});

const updateCounter = () => {
    const count = document.querySelectorAll("#media_castings div.form-group").length || document.querySelectorAll("#media_edit_castings div.form-group").length ;
    document.querySelector("#widgets-counter").value = count;
};

const handleDeleteButtons = () => {
    const deletes = document.querySelectorAll("button[data-action='delete']");
    deletes.forEach(button => {
        button.removeEventListener('click', deleteHandler); // Supprime l'ancien gestionnaire d'événements
        button.addEventListener('click', deleteHandler); // Ajoute le nouveau gestionnaire
    });
};

const deleteHandler = (event) => {
    const button = event.currentTarget;
    const target = button.dataset.target;
    const elementTarget = document.querySelector(target);
    if (elementTarget) {
        elementTarget.remove(); // Supprimer l'élément
        updateCounter(); // Mettre à jour le compteur après suppression
    }
};

updateCounter();
handleDeleteButtons();
