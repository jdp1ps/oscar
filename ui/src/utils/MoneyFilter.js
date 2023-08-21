export default {
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
};
