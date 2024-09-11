import { annotate } from 'rough-notation'; // Import RoughNotation

// Ajouter le code RoughNotation avec IntersectionObserver
document.addEventListener("DOMContentLoaded", function() {
    const element = document.querySelector('.lasso');
    const annotation = annotate(element, {
        type: 'highlight',  // Type d'annotation
        color: '#A051DE', // Couleur de surlignage (highlight)
        strokeWidth: 3,   // Épaisseur du trait
        padding: 5        // Espace autour du texte
    });

    // Utilisation de l'IntersectionObserver
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Ajouter un délai avant d'afficher l'annotation (par exemple, 1 seconde)
                setTimeout(() => {
                    annotation.show(); // Affiche l'annotation après le délai

                    // Après l'animation, changer la couleur du texte
                    setTimeout(() => {
                        element.style.color = '#FFFFFF'; // Change la couleur du texte 
                        element.style.transition="0.3s"
                    }, 500); // 500ms après l'annotation
                }, 1000); // Délai avant l'annotation (1 seconde)
                
                observer.unobserve(entry.target); // Stopper l'observation après l'affichage
            }
        });
    }, {
        threshold: 0.5 // Le pourcentage de l'élément visible avant de déclencher (50%)
    });

    observer.observe(element); // Observer l'élément avec la classe 'lasso'
});
