document.addEventListener('DOMContentLoaded', () => {
    const addImage = document.querySelector('#add-castings');
    if (!addImage) {
        console.error('Le bouton d\'ajout est introuvable.');
        return; 
    }

    addImage.addEventListener('click', () => {
        const widgetCounter = document.querySelector("#widgets-counter");
        const index = +widgetCounter.value;
        const annonceImages = document.querySelector("#media_castings") || document.querySelector("#media_edit_castings");

        const prototype = annonceImages.dataset.prototype.replace(/__name__/g, index);
        annonceImages.insertAdjacentHTML('beforeend', prototype);
        widgetCounter.value = index + 1;

        handleDeleteButtons(); 
        initializeChoices(); // Appelle la fonction d'initialisation ici
    });

    const initializeChoices = () => {
        const actorSelects = document.querySelectorAll('.choices-actor');
        actorSelects.forEach(select => {
            new Choices(select, {
                removeItemButton: true,
                searchEnabled: true,
                itemSelectText: '',
            });
        });
    };

    const updateCounter = () => {
        const count = document.querySelectorAll("#media_castings div.form-group, #media_edit_castings div.form-group").length;
        document.querySelector("#widgets-counter").value = count;
    };

    const handleDeleteButtons = () => {
        const deletes = document.querySelectorAll("button[data-action='delete']");
        deletes.forEach(button => {
            button.removeEventListener('click', deleteHandler);
            button.addEventListener('click', deleteHandler);
        });
    };

    const deleteHandler = (event) => {
        const button = event.currentTarget;
        const target = button.dataset.target;
        const elementTarget = document.querySelector(target);
        if (elementTarget) {
            elementTarget.remove(); 
            updateCounter(); 
        }
    };

    initializeChoices(); // Appelle l'initialisation au chargement de la page
    updateCounter();
    handleDeleteButtons();
});
