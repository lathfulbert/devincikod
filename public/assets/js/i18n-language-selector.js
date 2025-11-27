/**
 * I18n Language Selector - SunuFramework2
 *
 * Gère le changement de langue intégré avec le système I18n du framework
 * Utilise le style du template AbckOffice avec la fonctionnalité I18n native
 */

(function() {
    'use strict';

    /**
     * Initialise le sélecteur de langue
     */
    function initLanguageSelector() {
        const translateWrapper = document.querySelector('.translate_wrapper');
        if (!translateWrapper) return;

        const currentLang = translateWrapper.querySelector('.current_lang');
        const moreLang = translateWrapper.querySelector('.more_lang');
        const langOptions = translateWrapper.querySelectorAll('.more_lang .lang');

        // Toggle dropdown au clic sur la langue courante
        if (currentLang) {
            currentLang.addEventListener('click', function(e) {
                e.stopPropagation();
                translateWrapper.classList.toggle('active');
                moreLang.classList.toggle('active');
            });
        }

        // Sélection de langue avec redirection
        langOptions.forEach(function(option) {
            option.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                // Récupérer l'URL de redirection
                const langUrl = this.getAttribute('data-url');
                const langValue = this.getAttribute('data-value');

                if (langUrl) {
                    // Marquer visuellement comme sélectionné avant la redirection
                    this.classList.add('selected');
                    this.parentElement.querySelectorAll('.lang').forEach(function(lang) {
                        if (lang !== option) {
                            lang.classList.remove('selected');
                        }
                    });

                    // Redirection vers la nouvelle langue
                    // Le système I18n prendra le relais côté serveur
                    window.location.href = langUrl;
                }
            });
        });

        // Fermer le dropdown en cliquant ailleurs
        document.addEventListener('click', function(e) {
            if (!translateWrapper.contains(e.target)) {
                translateWrapper.classList.remove('active');
                moreLang.classList.remove('active');
            }
        });
    }

    /**
     * Point d'entrée principal
     * Attend que le DOM soit prêt avant d'initialiser
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLanguageSelector);
    } else {
        // DOM déjà prêt, initialiser immédiatement
        initLanguageSelector();
    }

    // Support pour les rechargements AJAX (si nécessaire)
    window.initLanguageSelector = initLanguageSelector;

})();
