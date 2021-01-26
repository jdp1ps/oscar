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
        var chaine = value.toFixed(2);
        var out = "";

        var pasteDigit = false;
        var count = 0;
        var out = [];

        for( var i=chaine.length-1; i>=0; i-- ){
            var char = chaine[i];
            if( char == '.' ){
                out.push(',');
                pasteDigit = true;
            } else {
                out.push(char);
                if( pasteDigit == true && char != '-' && i > 0){
                    count++;
                    if( count%3 == 0 ){
                        out.push(" ");
                    }
                }
            }
        }
        return out.reverse().join("");
    }
}
