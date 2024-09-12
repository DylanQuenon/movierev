import { annotate } from 'rough-notation'; // Import RoughNotation

// Ajouter le code RoughNotation avec IntersectionObserver
document.addEventListener("DOMContentLoaded", function() {
    const element = document.querySelector('.lasso');
    const annotation = annotate(element, {
        type: 'highlight',  // Type d'annotation (highlight pour surligner)
        color: '#A051DE', // Couleur de surlignage (mauve)
        strokeWidth: 3,   // Épaisseur du trait
        padding: 5        // Espace autour du texte
    });

    let observerInitialized = false;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (!observerInitialized) {
                    // Initialiser l'annotation uniquement lorsque l'élément entre dans le viewport
                    annotation.show();
                    observerInitialized = true; // Assurez-vous que l'annotation n'est pas réinitialisée
                }
            } else {
                // Réinitialiser l'annotation lorsque l'élément sort du viewport
                annotation.hide();
                observerInitialized = false; // Permet de réinitialiser l'annotation lorsqu'il revient
            }
        });
    }, {
        threshold: 0.5 // Le pourcentage de l'élément visible avant de déclencher (50%)
    });

    observer.observe(element); // Observer l'élément avec la classe 'lasso'
});
