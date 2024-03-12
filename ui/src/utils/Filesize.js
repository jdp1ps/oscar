export default {
    filesize(size, nullValue = "Inconnue") {
        if (size) {
            let unit = 'o';
            if (size > 1000) {
                size = Math.round(size / 1000);
                unit = 'Ko';
            }
            if (size > 1000) {
                size = Math.round(size / 1000);
                unit = 'Mo';
            }

            return size + " " + unit;
        } else {
            return nullValue
        }
    }
}