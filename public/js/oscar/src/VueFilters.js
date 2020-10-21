// nodejs node_modules/.bin/poi watch --format umd --moduleName  VueFilters --filename.js VueFilters.js --dist public/js/oscar/dist public/js/oscar/src/VueFilters.js

export default {
    slugify(text){
        return text.toString().toLowerCase()
            .replace(/\s+/g, '-')           // Replace spaces with -
            .replace(/[^\w\-]+/g, '')       // Remove all non-word chars
            .replace(/\-\-+/g, '-')         // Replace multiple - with single -
            .replace(/^-+/, '')             // Trim - from start of text
            .replace(/-+$/, '');            // Trim - from end of text
    },
    money(value){
        let entier = Math.floor(value);
        let decimal = Math.round((value - entier)*100);
        let modulo = entier % 1000;
        let out = entier.toString().replace(/\B(?=(\d{3})+(?!\d))/g, " ");
        if( decimal < 10 ) decimal = '0' + decimal;
        return out+ ',' +decimal;
    }
}
