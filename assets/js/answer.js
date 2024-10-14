document.addEventListener('DOMContentLoaded', () => {
    const addImage = document.querySelector('#add-answers');
    if (!addImage) {
        console.error('Le bouton d\'ajout est introuvable.');
        return; 
    }

    addImage.addEventListener('click', () => {
        const widgetCounter = document.querySelector("#widgets-counter");
        const index = +widgetCounter.value;
        const annonceImages = document.querySelector("#question_answers");

        const prototype = annonceImages.dataset.prototype.replace(/__name__/g, index);
        annonceImages.insertAdjacentHTML('beforeend', prototype);
        widgetCounter.value = index + 1;

        handleDeleteButtons(); 
    });

  

    const updateCounter = () => {
        const count = document.querySelectorAll("#question_answers div.form-group").length;
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


    updateCounter();
    handleDeleteButtons();
});
