import { annotate } from 'rough-notation'; // Import RoughNotation

/**
 * Initialise l'annotation sur l'élément spécifié et configure l'observateur d'intersection.
 * @param {string} selector - Le sélecteur CSS pour l'élément à annoter.
 * @param {Object} annotationOptions - Les options pour l'annotation RoughNotation.
 */
function initializeAnnotation(selector, annotationOptions) {
    const element = document.querySelector(selector);
    if (!element) return; // Assurez-vous que l'élément existe

    const annotation = annotate(element, annotationOptions);
    let observerInitialized = false;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                if (!observerInitialized) {
                    annotation.show();
                    observerInitialized = true;
                }
            } else {
                annotation.hide();
                observerInitialized = false;
            }
        });
    }, {
        threshold: 0.5 // Le pourcentage de l'élément visible avant de déclencher (50%)
    });

    observer.observe(element);
}

// Exemple d'utilisation pour l'élément avec la classe 'lasso'
initializeAnnotation('.lasso', {
    type: 'highlight',
    color: '#A051DE',
    strokeWidth: 4,
    padding: 15,
  
});

// Exemple d'utilisation pour l'élément avec la classe 'annotation'
initializeAnnotation('.annotation', {
    type: 'underline',
    color: '#A051DE',
    strokeWidth: 3,
    padding: 15,
    iterations: 3,
    animationDuration: 3000,
});

