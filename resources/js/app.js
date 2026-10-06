import Alpine from 'alpinejs';

window.Alpine = Alpine;

/**
 * Formulier met verplichte velden: `complete` wordt true zodra alle verplichte velden goed zijn
 * ingevuld (volgens de controle van de browser, dus ook e-mailformaat en patronen).
 * Gebruik: <form x-data="requiredForm({ ... })" @input="check" @change="check">
 * Extra Alpine-gegevens voor het formulier kunnen als object worden meegegeven.
 */
window.requiredForm = (extra = {}) => ({
    complete: false,

    init() {
        this.check();
    },

    check() {
        // Wachten tot Alpine de pagina heeft bijgewerkt, bijvoorbeeld een veld dat verplicht wordt
        this.$nextTick(() => {
            this.complete = this.$root.checkValidity();
        });
    },

    ...extra,
});

Alpine.start();
