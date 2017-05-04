var InTheBox = {
  version: "0.0.1",
  authors: ["Stéphane Bouvry"],

  Color:{
    /**
     * Convert hexa color to rgba color
     */
    hexaToRgba(hexa){
      let r = hexa.substr(1,2),
          g = hexa.substr(3,2),
          b = hexa.substr(5,2),
          a = hexa.substr(7,2),
          rgba;
      r = parseInt(r, 16);
      g = parseInt(g, 16);
      b = parseInt(b, 16);
      a = a ? parseInt(a, 16) / 255 : 1;

      return `rgba(${r},${g},${b},${a})`;
    },

    /**
     * Generate random colors.
     */
    generateColor(nbr, opacity = '', format='hexa'){
      const total = 16777215;

      let pas = Math.floor(total/nbr)
      ,   colors = []
      ,   pattern = 'FFFFFF';

      if( format == 'rgba' ){
        format = InTheBox.Color.hexaToRgba;
      } else {
        format = (v) => v;
      }

      for( let i=0; i<nbr; i++ ){
        let intval = Math.round(Math.random() * pas + pas * i)
        ,   hex = intval.toString(16);
        hex = '#' + pattern.substring(0, pattern.length - hex.length) + hex;
        colors.push(format(hex + opacity));
      }
      
      return colors;
    }
  }
};
