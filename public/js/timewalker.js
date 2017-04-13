(function (factory) {
    if (typeof define === 'function' && define.amd) {
        define(['jquery'], factory); // Utiliser l'AMD
    } else {
        factory(jQuery); // pas d'AMD
    }
}(function ($) {
    /**
     * TIMEWALKER ©2015
     * @author  Stéphane Bouvry<jacksay@jacksay.com>
     */
    $.fn.timewalker = function (options) {

        var controllers = [];

        this.each(function (i, controller) {
            // Data for this controller
            var
                $controller = $(controller),
                $displayYear = $controller.find('[data-tw-display-year]'),
                year = parseInt($controller.data('year')),
                serie = $controller.data('tw-targetserie'),
                $today = $('[data-tw-serie=' + serie + '] .timewalker-today'),
                datas = $('[data-tw-serie=' + serie + '] .timewalker-data');

            $controller.find('[data-tw-nextyear]').on('click', function (e) {
                e.preventDefault();
                year++;
                $displayYear.text(year);
                displayData(datas, year);
            });

            $controller.find('[data-tw-previousyear]').on('click', function (e) {
                e.preventDefault();
                year--;
                $displayYear.text(year);
                displayData(datas, year);
            });

            function displayData(datas, year) {

                var begin = new Date(year + '-01-01').getTime(),
                    end = new Date(year + '-12-31').getTime(),
                    today = new Date().getTime(),
                    ratio = 100 / (end - begin);
                if (today > begin && today < end) {
                    $today.show().css({
                        position: 'absolute',
                        top: 0,
                        bottom: 0,
                        left: ((today - begin) * ratio) + '%',
                        right: 0
                    });
                } else {
                    $today.hide();
                }
                datas.each(function (i, data) {
                    //          console.log(data);
                    var $data = $(data),
                        dStart = $data.data('tw-datestart'),
                        dEnd = $data.data('tw-dateend'),
                        dateStart = dStart ? new Date(dStart).getTime() : null,
                        dateEnd = dEnd ? new Date(dEnd).getTime() : null
                        ;

                    //

                    if (dateStart == null) {
                        $data.addClass('infinite-left');
                        dateStart = begin;
                    }
                    if (dateEnd == null) {
                        $data.addClass('infinite-right');
                        dateEnd = end;
                    }

                    // Hors période affichée
                    if (dateEnd <= begin || dateStart >= end) {
                        $data.hide();
                        return;
                    }

                    //
                    if (today > dateEnd || today < dateStart) {
                        $data.addClass('not-today')
                    } else {
                        $data.removeClass('not-today')

                    }

                    if (dateStart < begin) {
                        dateStart = begin;
                    }

                    if (dateEnd > end) {
                        dateEnd = end;
                    }

                    dateStart -= begin;
                    dateEnd -= begin;

                    dateStart *= ratio;
                    dateEnd *= ratio;

                    $data.css({
                        'display': 'block',
                        'position': 'absolute',
                        'left': dateStart + '%',
                        'right': (100 - dateEnd) + '%'
                    });
                });
            }

            displayData(datas, year);
        });
    };
}));
