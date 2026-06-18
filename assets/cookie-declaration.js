(function() {
    'use strict';

    var CMP_STORAGE_KEY = 'cmp_consent';
    var CMP_ANON_ID_KEY = 'cmp_anon_id';

    // Safe fetch — some sites override window.fetch with custom functions (e.g. WordPress AJAX search)
    var nativeFetch = (function() {
        if (window.fetch && /\[native code\]/.test(Function.prototype.toString.call(window.fetch))) {
            return window.fetch.bind(window);
        }
        // Fallback: XMLHttpRequest wrapped as fetch-like promise
        return function(url) {
            return new Promise(function(resolve, reject) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', url);
                xhr.onload = function() {
                    resolve({
                        ok: xhr.status >= 200 && xhr.status < 300,
                        status: xhr.status,
                        json: function() { return Promise.resolve(JSON.parse(xhr.responseText)); }
                    });
                };
                xhr.onerror = function() { reject(new Error('Network error')); };
                xhr.send();
            });
        };
    })();

    var TRANSLATIONS = {
        de: {
            title: 'Cookie-Erkl\u00e4rung',
            description: 'Auf {site} werden folgende Dienste und Cookies verwendet:',
            privacyLink: 'Datenschutz',
            empty: 'Es wurden noch keine Dienste erkannt.',
            powered: 'Powered by',
            loading: 'Cookie-Erkl\u00e4rung wird geladen\u2026',
            error: 'Die Cookie-Erkl\u00e4rung konnte nicht geladen werden.',
            categories: {
                necessary: 'Notwendig',
                functional: 'Funktional',
                analytics: 'Statistik',
                marketing: 'Marketing'
            },
            intro: 'Diese Webseite verwendet Cookies. Wir verwenden Cookies, um Inhalte und Anzeigen zu personalisieren, Funktionen f\u00fcr soziale Medien anbieten zu k\u00f6nnen und die Zugriffe auf unsere Website zu analysieren. Ausserdem geben wir Informationen zu Ihrer Verwendung unserer Website an unsere Partner f\u00fcr soziale Medien, Werbung und Analysen weiter. Unsere Partner f\u00fchren diese Informationen m\u00f6glicherweise mit weiteren Daten zusammen, die Sie ihnen bereitgestellt haben oder die sie im Rahmen Ihrer Nutzung der Dienste gesammelt haben.',
            introWhat: 'Cookies sind kleine Textdateien, die von Webseiten verwendet werden, um die Benutzererfahrung effizienter zu gestalten.',
            introLegal: 'Laut Gesetz k\u00f6nnen wir Cookies auf Ihrem Ger\u00e4t speichern, wenn diese f\u00fcr den Betrieb dieser Seite unbedingt notwendig sind. F\u00fcr alle anderen Cookie-Typen ben\u00f6tigen wir Ihre Erlaubnis. Diese Seite verwendet unterschiedliche Cookie-Typen. Einige Cookies werden von Drittparteien platziert, die auf unseren Seiten erscheinen.',
            consentDomain: 'Ihre Einwilligung trifft auf folgende Domain zu: {domain}',
            consentStatus: 'Ihr aktueller Zustand:',
            consentDate: 'Einwilligungsdatum:',
            consentId: 'Einwilligungs-ID:',
            consentChange: 'Einwilligung \u00e4ndern',
            consentRevoke: 'Einwilligung widerrufen',
            consentNone: 'Keine Einwilligung erteilt.',
            statusAcceptAll: 'Alle akzeptiert',
            statusRejectAll: 'Alle abgelehnt',
            statusCustom: 'Benutzerdefiniert'
        },
        fr: {
            title: 'D\u00e9claration relative aux cookies',
            description: 'Sur {site}, les services et cookies suivants sont utilis\u00e9s\u00a0:',
            privacyLink: 'Confidentialit\u00e9',
            empty: 'Aucun service n\u2019a encore \u00e9t\u00e9 d\u00e9tect\u00e9.',
            powered: 'Propuls\u00e9 par',
            loading: 'Chargement de la d\u00e9claration relative aux cookies\u2026',
            error: 'La d\u00e9claration relative aux cookies n\u2019a pas pu \u00eatre charg\u00e9e.',
            categories: {
                necessary: 'N\u00e9cessaires',
                functional: 'Fonctionnels',
                analytics: 'Statistiques',
                marketing: 'Marketing'
            },
            intro: 'Ce site web utilise des cookies. Nous utilisons des cookies pour personnaliser le contenu et les publicit\u00e9s, pour proposer des fonctionnalit\u00e9s de m\u00e9dias sociaux et pour analyser le trafic de notre site. Nous partageons \u00e9galement des informations sur votre utilisation de notre site avec nos partenaires de m\u00e9dias sociaux, de publicit\u00e9 et d\u2019analyse. Nos partenaires peuvent combiner ces informations avec d\u2019autres donn\u00e9es que vous leur avez fournies ou qu\u2019ils ont collect\u00e9es dans le cadre de votre utilisation de leurs services.',
            introWhat: 'Les cookies sont de petits fichiers texte utilis\u00e9s par les sites web pour rendre l\u2019exp\u00e9rience utilisateur plus efficace.',
            introLegal: 'Selon la loi, nous pouvons stocker des cookies sur votre appareil s\u2019ils sont strictement n\u00e9cessaires au fonctionnement de ce site. Pour tous les autres types de cookies, nous avons besoin de votre autorisation. Ce site utilise diff\u00e9rents types de cookies. Certains cookies sont plac\u00e9s par des tiers qui apparaissent sur nos pages.',
            consentDomain: 'Votre consentement s\u2019applique au domaine suivant\u00a0: {domain}',
            consentStatus: 'Votre \u00e9tat actuel\u00a0:',
            consentDate: 'Date du consentement\u00a0:',
            consentId: 'ID du consentement\u00a0:',
            consentChange: 'Modifier le consentement',
            consentRevoke: 'R\u00e9voquer le consentement',
            consentNone: 'Aucun consentement donn\u00e9.',
            statusAcceptAll: 'Tout accept\u00e9',
            statusRejectAll: 'Tout refus\u00e9',
            statusCustom: 'Personnalis\u00e9'
        },
        it: {
            title: 'Dichiarazione sui cookie',
            description: 'Su {site} vengono utilizzati i seguenti servizi e cookie:',
            privacyLink: 'Privacy',
            empty: 'Non sono ancora stati rilevati servizi.',
            powered: 'Powered by',
            loading: 'Caricamento della dichiarazione sui cookie\u2026',
            error: 'Impossibile caricare la dichiarazione sui cookie.',
            categories: {
                necessary: 'Necessari',
                functional: 'Funzionali',
                analytics: 'Statistiche',
                marketing: 'Marketing'
            },
            intro: 'Questo sito web utilizza i cookie. Utilizziamo i cookie per personalizzare contenuti e annunci, per fornire funzionalit\u00e0 dei social media e per analizzare il traffico del nostro sito. Condividiamo inoltre informazioni sull\u2019utilizzo del nostro sito con i nostri partner di social media, pubblicit\u00e0 e analisi. I nostri partner possono combinare queste informazioni con altri dati che avete fornito loro o che hanno raccolto nell\u2019ambito del vostro utilizzo dei loro servizi.',
            introWhat: 'I cookie sono piccoli file di testo utilizzati dai siti web per rendere pi\u00f9 efficiente l\u2019esperienza dell\u2019utente.',
            introLegal: 'Per legge, possiamo memorizzare cookie sul vostro dispositivo se sono strettamente necessari per il funzionamento di questo sito. Per tutti gli altri tipi di cookie abbiamo bisogno del vostro consenso. Questo sito utilizza diversi tipi di cookie. Alcuni cookie vengono inseriti da terze parti che compaiono sulle nostre pagine.',
            consentDomain: 'Il vostro consenso si applica al seguente dominio: {domain}',
            consentStatus: 'Il vostro stato attuale:',
            consentDate: 'Data del consenso:',
            consentId: 'ID del consenso:',
            consentChange: 'Modificare il consenso',
            consentRevoke: 'Revocare il consenso',
            consentNone: 'Nessun consenso fornito.',
            statusAcceptAll: 'Tutto accettato',
            statusRejectAll: 'Tutto rifiutato',
            statusCustom: 'Personalizzato'
        },
        en: {
            title: 'Cookie Declaration',
            description: 'The following services and cookies are used on {site}:',
            privacyLink: 'Privacy',
            empty: 'No services have been detected yet.',
            powered: 'Powered by',
            loading: 'Loading cookie declaration\u2026',
            error: 'The cookie declaration could not be loaded.',
            categories: {
                necessary: 'Necessary',
                functional: 'Functional',
                analytics: 'Analytics',
                marketing: 'Marketing'
            },
            intro: 'This website uses cookies. We use cookies to personalise content and ads, to provide social media features and to analyse our traffic. We also share information about your use of our site with our social media, advertising and analytics partners. Our partners may combine this information with other data that you have provided to them or that they have collected as part of your use of their services.',
            introWhat: 'Cookies are small text files that are used by websites to make the user experience more efficient.',
            introLegal: 'According to the law, we can store cookies on your device if they are strictly necessary for the operation of this site. For all other types of cookies, we need your permission. This site uses different types of cookies. Some cookies are placed by third parties that appear on our pages.',
            consentDomain: 'Your consent applies to the following domain: {domain}',
            consentStatus: 'Your current state:',
            consentDate: 'Consent date:',
            consentId: 'Consent ID:',
            consentChange: 'Change consent',
            consentRevoke: 'Revoke consent',
            consentNone: 'No consent given.',
            statusAcceptAll: 'All accepted',
            statusRejectAll: 'All rejected',
            statusCustom: 'Custom'
        }
    };

    function t(lang) {
        return TRANSLATIONS[lang] || TRANSLATIONS.de;
    }

    function escapeHtml(str) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str || ''));
        return div.innerHTML;
    }

    function getStoredConsent() {
        try {
            var stored = localStorage.getItem(CMP_STORAGE_KEY);
            if (!stored) return null;
            return JSON.parse(stored);
        } catch (e) {
            return null;
        }
    }

    function getAnonId() {
        try {
            return localStorage.getItem(CMP_ANON_ID_KEY) || null;
        } catch (e) {
            return null;
        }
    }

    function formatDate(isoString, lang) {
        try {
            var date = new Date(isoString);
            var locale = lang === 'de' ? 'de-CH' : lang === 'fr' ? 'fr-CH' : lang === 'it' ? 'it-CH' : 'en-GB';
            return date.toLocaleString(locale, {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit'
            });
        } catch (e) {
            return isoString;
        }
    }

    function getConsentStatusLabel(consent, strings) {
        if (!consent) return strings.consentNone;
        if (consent.decision === 'accept') return strings.statusAcceptAll;
        if (consent.decision === 'reject') return strings.statusRejectAll;
        return strings.statusCustom;
    }

    function injectStyles() {
        if (document.getElementById('crumbler-cd-styles')) return;
        var style = document.createElement('style');
        style.id = 'crumbler-cd-styles';
        style.textContent =
            '.crumbler-cookies{font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,Oxygen,Ubuntu,sans-serif;line-height:1.6;font-size:15px;color:#1f2937}' +
            '.crumbler-cd-header{margin-bottom:24px}' +
            '.crumbler-cd-title{font-size:24px;font-weight:700;margin:0 0 8px 0}' +
            '.crumbler-cd-description{opacity:.7;font-size:15px;margin:0}' +
            '.crumbler-cd-intro{margin-bottom:24px;padding:20px;background:#f9fafb;border-radius:8px;font-size:14px;line-height:1.7}' +
            '.crumbler-cd-intro p{margin:0 0 12px 0}' +
            '.crumbler-cd-intro p:last-child{margin-bottom:0}' +
            '.crumbler-cd-consent-box{margin-bottom:24px;padding:16px 20px;background:#fff;border-radius:8px;box-shadow:0 1px 3px rgba(0,0,0,.08);font-size:14px}' +
            '.crumbler-cd-consent-box p{margin:0 0 6px 0}' +
            '.crumbler-cd-consent-box p:last-child{margin-bottom:0}' +
            '.crumbler-cd-consent-label{font-weight:600}' +
            '.crumbler-cd-consent-actions{margin-top:12px;display:flex;gap:16px;font-size:14px}' +
            '.crumbler-cd-consent-actions a,.crumbler-cd-consent-actions button{color:#a37b61;text-decoration:none;background:none;border:none;cursor:pointer;padding:0;font-size:inherit;font-family:inherit}' +
            '.crumbler-cd-consent-actions a:hover,.crumbler-cd-consent-actions button:hover{text-decoration:underline}' +
            '.crumbler-cd-category{background:#fff;border-radius:8px;margin-bottom:16px;box-shadow:0 1px 3px rgba(0,0,0,.08);overflow:hidden}' +
            '.crumbler-cd-category-header{padding:14px 20px;font-weight:600;font-size:16px;border-bottom:1px solid rgba(0,0,0,.06);display:flex;align-items:center;gap:10px}' +
            '.crumbler-cd-badge{font-size:12px;font-weight:500;padding:2px 10px;border-radius:12px;color:#fff}' +
            '.crumbler-cd-badge-necessary{background:#6b7280}' +
            '.crumbler-cd-badge-functional{background:#8b5cf6}' +
            '.crumbler-cd-badge-analytics{background:#f59e0b}' +
            '.crumbler-cd-badge-marketing{background:#ef4444}' +
            '.crumbler-cd-provider{padding:14px 20px;border-bottom:1px solid rgba(0,0,0,.04)}' +
            '.crumbler-cd-provider:last-child{border-bottom:none}' +
            '.crumbler-cd-provider-header{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:4px}' +
            '.crumbler-cd-provider-name{font-weight:600;font-size:15px}' +
            '.crumbler-cd-provider-link{font-size:13px;color:#a37b61;text-decoration:none;white-space:nowrap}' +
            '.crumbler-cd-provider-link:hover{text-decoration:underline}' +
            '.crumbler-cd-provider-description{font-size:14px;opacity:.75;line-height:1.5}' +
            '.crumbler-cd-empty{text-align:center;padding:40px 20px;opacity:.5}' +
            '.crumbler-cd-loading{text-align:center;padding:40px 20px;opacity:.5}' +
            '.crumbler-cd-error{text-align:center;padding:40px 20px;color:#ef4444}' +
            '.crumbler-cd-footer{margin-top:24px;text-align:center;font-size:13px;opacity:.5}' +
            '.crumbler-cd-footer a{color:inherit}' +
            '@media(max-width:640px){.crumbler-cd-provider-header{flex-direction:column;align-items:flex-start;gap:4px}.crumbler-cd-consent-actions{flex-direction:column;gap:8px}}';
        document.head.appendChild(style);
    }

    function render(container) {
        var siteKey = container.getAttribute('data-site-key');
        var lang = container.getAttribute('data-lang') || 'de';
        var apiUrl = container.getAttribute('data-api-url');
        var strings = t(lang);

        if (!siteKey || !apiUrl) {
            container.innerHTML = '<p class="crumbler-cd-error">' + escapeHtml(strings.error) + '</p>';
            return;
        }

        container.innerHTML = '<p class="crumbler-cd-loading">' + escapeHtml(strings.loading) + '</p>';

        var url = apiUrl + '?siteKey=' + encodeURIComponent(siteKey) + '&lang=' + encodeURIComponent(lang);

        nativeFetch(url)
            .then(function(response) {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(function(data) {
                container.innerHTML = buildHTML(data, strings, lang);
                bindConsentActions(container);
            })
            .catch(function() {
                container.innerHTML = '<p class="crumbler-cd-error">' + escapeHtml(strings.error) + '</p>';
            });
    }

    function bindConsentActions(container) {
        var changeBtn = container.querySelector('[data-ccmp-action="change"]');
        var revokeBtn = container.querySelector('[data-ccmp-action="revoke"]');

        if (changeBtn) {
            changeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.CMP && window.CMP.show) {
                    window.CMP.show();
                }
            });
        }

        if (revokeBtn) {
            revokeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                if (window.CMP && window.CMP.revokeConsent) {
                    window.CMP.revokeConsent();
                }
            });
        }
    }

    function buildHTML(data, strings, lang) {
        var html = '';
        var siteName = data.site_name || '';
        var categories = data.categories || {};
        var categoryOrder = ['necessary', 'functional', 'analytics', 'marketing'];
        var hasProviders = false;
        var domain = window.location.hostname;

        // Header
        html += '<div class="crumbler-cd-header">';
        html += '<h2 class="crumbler-cd-title">' + escapeHtml(strings.title) + '</h2>';
        html += '</div>';

        // Intro text
        html += '<div class="crumbler-cd-intro">';
        html += '<p>' + escapeHtml(strings.intro) + '</p>';
        html += '<p>' + escapeHtml(strings.introWhat) + '</p>';
        html += '<p>' + escapeHtml(strings.introLegal) + '</p>';
        html += '</div>';

        // Consent status box
        var consent = getStoredConsent();
        var anonId = getAnonId();

        html += '<div class="crumbler-cd-consent-box">';
        html += '<p>' + escapeHtml(strings.consentDomain.replace('{domain}', domain)) + '</p>';

        if (consent) {
            html += '<p><span class="crumbler-cd-consent-label">' + escapeHtml(strings.consentStatus) + '</span> ' + escapeHtml(getConsentStatusLabel(consent, strings)) + '</p>';
            if (consent.timestamp) {
                html += '<p><span class="crumbler-cd-consent-label">' + escapeHtml(strings.consentDate) + '</span> ' + escapeHtml(formatDate(consent.timestamp, lang)) + '</p>';
            }
            if (anonId) {
                html += '<p><span class="crumbler-cd-consent-label">' + escapeHtml(strings.consentId) + '</span> ' + escapeHtml(anonId) + '</p>';
            }
            html += '<div class="crumbler-cd-consent-actions">';
            html += '<button type="button" data-ccmp-action="change">' + escapeHtml(strings.consentChange) + '</button>';
            html += '<button type="button" data-ccmp-action="revoke">' + escapeHtml(strings.consentRevoke) + '</button>';
            html += '</div>';
        } else {
            html += '<p>' + escapeHtml(strings.consentNone) + '</p>';
        }
        html += '</div>';

        // Description
        if (siteName) {
            html += '<p class="crumbler-cd-description" style="margin-bottom:20px">' + escapeHtml(strings.description.replace('{site}', siteName)) + '</p>';
        }

        // Categories
        for (var i = 0; i < categoryOrder.length; i++) {
            var catKey = categoryOrder[i];
            var providers = categories[catKey];
            if (!providers || providers.length === 0) continue;
            hasProviders = true;

            html += '<div class="crumbler-cd-category">';
            html += '<div class="crumbler-cd-category-header">';
            html += escapeHtml(strings.categories[catKey] || catKey);
            html += ' <span class="crumbler-cd-badge crumbler-cd-badge-' + catKey + '">' + providers.length + '</span>';
            html += '</div>';

            for (var j = 0; j < providers.length; j++) {
                var provider = providers[j];
                html += '<div class="crumbler-cd-provider">';
                html += '<div class="crumbler-cd-provider-header">';
                html += '<span class="crumbler-cd-provider-name">' + escapeHtml(provider.name) + '</span>';
                if (provider.privacy_url) {
                    html += '<a href="' + escapeHtml(provider.privacy_url) + '" target="_blank" rel="noopener" class="crumbler-cd-provider-link">';
                    html += escapeHtml(strings.privacyLink) + ' \u2197</a>';
                }
                html += '</div>';
                if (provider.description) {
                    html += '<div class="crumbler-cd-provider-description">' + escapeHtml(provider.description) + '</div>';
                }
                html += '</div>';
            }

            html += '</div>';
        }

        if (!hasProviders) {
            html += '<div class="crumbler-cd-empty"><p>' + escapeHtml(strings.empty) + '</p></div>';
        }

        html += '<div class="crumbler-cd-footer">' + escapeHtml(strings.powered) + ' <a href="https://crumbler.ch" target="_blank" rel="noopener">Crumbler</a></div>';

        return html;
    }

    function init() {
        var containers = document.querySelectorAll('.crumbler-cookies');
        if (containers.length === 0) return;
        injectStyles();
        for (var i = 0; i < containers.length; i++) {
            render(containers[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
