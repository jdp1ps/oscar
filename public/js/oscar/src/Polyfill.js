//
console.log("Load POLYFILL UNICAEN");


Array.prototype.testTata = function(){
    console.log('TEST TATA : ', this);
};

////////////////////////////////////////////////////////////////////////////////
//
// ARRAY
//
////////////////////////////////////////////////////////////////////////////////
Array.prototype.pushIfNot = function(obj, testField){
    var add = true,
        val = obj[testField];

    for( var i=0; i<this.length; i++ ){
        if(this[i][testField] == val) add = false;
    }
    if( add )
        this.push(obj)
};
