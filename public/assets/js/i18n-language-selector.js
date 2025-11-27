/**
 * I18n Language Selector - SunuFramework2
 *
 * Gère le changement de langue intégré avec le système I18n du framework
 * Utilise le style du template AbckOffice avec la fonctionnalité I18n native
 */

(function() {
    'use strict';

    console.log('I18n Language Selector: Initializing...');

    /**
     * Initialise le sélecteur de langue
     */
    function initLanguageSelector() {
        const wrappers = document.querySelectorAll('.translate_wrapper');
        console.log('I18n Language Selector: Found ' + wrappers.length + ' wrappers');

        if (wrappers.length === 0) return;

        wrappers.forEach(function(translateWrapper) {
            const currentLang = translateWrapper.querySelector('.current_lang');
            const moreLang = translateWrapper.querySelector('.more_lang');
            const langOptions = translateWrapper.querySelectorAll('.more_lang .lang');

            // Toggle dropdown au clic sur la langue courante
            if (currentLang) {
                currentLang.addEventListener('click', function(e) {
                    e.stopPropagation();
                    translateWrapper.classList.toggle('active');
                    moreLang.classList.toggle('active');
                    console.log('I18n Language Selector: Toggled active state');
                });
            }

            // Sélection de langue avec redirection
            langOptions.forEach(function(option) {
                option.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Récupérer l'URL de redirection
                    const langUrl = this.getAttribute('data-url');
                    
                    if (langUrl) {
                        console.log('I18n Language Selector: Redirecting to ' + langUrl);
                        window.location.href = langUrl;
                    }
                });
            });

            // Fermer le dropdown en cliquant ailleurs
            document.addEventListener('click', function(e) {
                if (!translateWrapper.contains(e.target)) {
                    translateWrapper.classList.remove('active');
                    if (moreLang) moreLang.classList.remove('active');
                }
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLanguageSelector);
    } else {
        initLanguageSelector();
    }

    window.initLanguageSelector = initLanguageSelector;

})();
