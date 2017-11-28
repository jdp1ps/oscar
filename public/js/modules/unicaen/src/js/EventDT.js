//import moment from "moment";

moment.locale('fr');

var EventDT = class {
    constructor(data) {
        this.sync(data);
    }

    get isLocked(){
        return !(this.sendable || this.validableAdm || this.validableSci || this.editable || this.deletable);
    }

    get isSend(){
        return this.status == 'send';
    }

    get isInfo(){
        return this.status == 'info';
    }

    get isValidSci(){
        return this.validatedSciAt != null;
    }

    get isValidAdmin(){
        return this.validatedAdminAt != null;
    }

    get isRejecteSci(){
        return this.rejectedSciAt != null;
    }

    get isRejecteAdmin(){
        return this.rejectedAdminAt != null;
    }

    get isValid(){
        return this.isValidAdmin && this.isValidSci;
    }

    get isReject(){
        return this.isRejecteAdmin || this.isRejecteSci;
    }

    /**
     * Retourne un objet moment pour la date de début.
     */
    get mmStart() {
        return moment(this.start)
    }

    /**
     * Retourne un objet moment pour la date de fin.
     */
    get mmEnd() {
        return moment(this.end)
    }

    /**
     * Retourne la durée de l'événement en minutes.
     * @returns {number}
     */
    get durationMinutes() {
        return (this.mmEnd.unix() - this.mmStart.unix()) / 60;
    }

    /**
     * Retourne la durée de l'événement en heure.
     * @returns {number}
     */
    get duration() {
        return this.durationMinutes / 60;
    }

    get dayTime() {
        return "de " + this.mmStart.format('hh:mm')
            + " à " + this.mmEnd.format('hh:mm')
            + ", le " + this.mmStart.format('dddd D MMMM YYYY');
    }

    /**
     * Test si l'événement est présent dans la semaine.
     * @return boolean
     */
    inWeek(year, week) {
        let mmStart = this.mmStart.unix(),
            mmEnd = this.mmEnd.unix();

        // Récupération de la plage de la semaine
        let weekStart = moment().year(year).week(week).startOf('week'),
            plageStart = weekStart.unix(),
            plageFin = weekStart.endOf('week').unix();

        if (mmStart > plageFin || mmEnd < plageStart)
            return false

        return mmStart < plageFin || mmEnd > plageStart;
    }

    overlap(otherEvent) {
        let startU1 = this.mmStart.unix()
            , endU1 = this.mmEnd.unix()
            , startU2 = otherEvent.mmStart.unix()
            , endU2 = otherEvent.mmEnd.unix()
        ;
        return startU1 < endU2 && startU2 < endU1;
    }

    isBefore(eventDT) {
        if (this.mmStart < eventDT.mmStart) {
            return true;
        }
        return false;
    }

    sync(data) {
        this.id = data.id;
        this.label = data.label;
        this.description = data.description == 'undefined' ? '' : data.description;
        this.start = data.start;
        this.end = data.end;
        this.icsfileuid = data.icsfileuid;
        this.icsfilename = data.icsfilename;
        this.icsfiledateadded = data.icsfiledateadded;
        this.icsuid = data.icsuid;
        this.status = data.status;

        if( !this.uid ){
            this.uid = EventDT.UID++;
        }

        this.workpackageId = data.workpackage_id || null;
        this.workpackageCode = data.workpackage_code || null;
        this.workpackageLabel = data.workpackage_label || null;

        this.activityId = data.activity_id || null;
        this.activityLabel = data.activity_label || null;

        this.owner = data.owner;
        this.owner_id = data.owner_id;
        this.decaleY = 0;

        this.rejectedComment = data.rejectedComment;
        this.rejectedCommentAt = data.rejectedCommentAt;
        this.rejectedAdminComment = data.rejectedAdminComment;
        this.rejectedAdminCommentAt = data.rejectedAdminCommentAt;

        this.rejectedSciComment = data.rejectedSciComment;
        this.rejectedSciAt = data.rejectedSciAt;
        this.rejectedSciBy = data.rejectedSciBy;
        this.rejectedAdminComment = data.rejectedAdminComment;
        this.rejectedAdminAt = data.rejectedAdminAt;
        this.rejectedAdminBy = data.rejectedAdminBy;

        this.validatedSciAt = data.validatedSciAt;
        this.validatedSciBy = data.validatedSciBy;
        this.validatedAdminAt = data.validatedAdminAt;
        this.validatedAdminBy = data.validatedAdminBy;

        this.editable = false;
        this.deletable = false;
        this.validable = false;
        this.validableAdm = false;
        this.validableSci = false;
        this.sendable = false;

        if (data.credentials) {
            this.editable = data.credentials.editable;
            this.deletable = data.credentials.deletable;
            this.validable = false;
            this.validableAdm = data.credentials.validableAdm;
            this.validableSci = data.credentials.validableSci;
            this.sendable = data.credentials.sendable;
        }
    }

    static first(events) {
        var first = null;
        events.forEach((e1) => {
            if (first == null) {
                first = e1;
            } else {
                if (e1.isBefore(first)) {
                    first = e1;
                }
            }
        });
        return first;
    }


    static sortByStart(events) {
        var sorted = events.sort((e1, e2) => {
            if (e1.mmStart < e2.mmStart)
                return -1

            else if (e1.mmStart > e2.mmStart)
                return 1

            return 0;
        });
        return sorted;
    }
}

EventDT.UID = 1
