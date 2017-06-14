//import moment from "moment";

moment.locale('fr');

var EventDT = class {
  constructor(id, label, start, end, description="",
              actions={}, status='draft', owner="", owner_id = null,
              rejectedSciComment="", rejectedSciAt=null,
              rejectedAdminComment="", rejectedAdminAt=null,
              validatedSciAt = null, validatedSciBy = null,
              validatedAdminAt = null, validatedAdminBy = null
            ){
    this.id = id;

    // From ICS format
    this.uid = EventDT.UID++;

    // ICS : summary
    this.label = label;


    // ICS : description
    this.description = description;

    this.owner = owner;
    this.owner_id = owner_id;
    this.intersect = 0;
    this.intersectIndex = 0;

    this.rejectedSciComment = rejectedSciComment;
    this.rejectedSciAt = rejectedSciAt;
    this.rejectedAdminComment = rejectedAdminComment;
    this.rejectedAdminAt = rejectedAdminAt;

    this.validatedSciAt = validatedSciAt;
    this.validatedSciBy = validatedSciBy;
    this.validatedAdminAt = validatedAdminAt;
    this.validatedAdminBy = validatedAdminBy;



    // OSCAR
    this.editable = actions.editable || false;
    this.deletable = actions.deletable || false;
    this.validable = actions.validable || false;
    this.sendable= actions.sendable || false;
    this.validableSci= actions.validableSci || false;
    this.validableAdm= actions.validableAdm || false;

    // Status
    // - DRAFT, SEND, VALID, REJECT
    this.status = status;

    this.start = start;
    this.end = end;
  }

  /**
   * Retourne un objet moment pour la date de début.
   */
  get mmStart(){
    return moment(this.start)
  }

  /**
   * Retourne un objet moment pour la date de fin.
   */
  get mmEnd(){
    return moment(this.end)
  }

  /**
   * Retourne la durée de l'événement en minutes.
   * @returns {number}
   */
  get durationMinutes(){
    return (this.mmEnd.unix() - this.mmStart.unix())/60;
  }

  /**
   * Retourne la durée de l'événement en heure.
   * @returns {number}
   */
  get duration(){
    return this.durationMinutes / 60;
  }

  get dayTime(){
    return "de " + this.mmStart.format('hh:mm')
        + " à " + this.mmEnd.format('hh:mm')
        + ", le " + this.mmStart.format('dddd D MMMM YYYY');
  }

  /**
   * Test si l'événement est présent dans la semaine.
   * @return boolean
   */
  inWeek(year, week){
    let mmStart = this.mmStart.unix(),
        mmEnd = this.mmEnd.unix();

    // Récupération de la plage de la semaine
    let weekStart = moment().year(year).week(week).startOf('week'),
      plageStart = weekStart.unix(),
      plageFin = weekStart.endOf('week').unix();

    if( mmStart > plageFin || mmEnd < plageStart )
          return false

    return mmStart < plageFin || mmEnd > plageStart;
  }

  overlap(otherEvent){
    let   startU1 = this.mmStart.unix()
        , endU1 = this.mmEnd.unix()
        , startU2 = otherEvent.mmStart.unix()
        , endU2 = otherEvent.mmEnd.unix()
        ;
    return startU1 < endU2 && startU2 < endU1;
  }

  isBefore( eventDT ){
    if( this.mmStart < eventDT.mmStart ){
      return true;
    }
    return false;
  }

  sync( data ){
    console.log("Synchronisation de l'événement", this.id, "avec", data);

    if( data.id ) this.id = data.id;
    if( data.label ) this.label = data.label;
    if( data.description ) this.description = data.description;
    if( data.start ) this.start = data.start;
    if( data.end ) this.end = data.end;
    if( data.status ) this.status = data.status;
    if( data.rejectedComment ) this.rejectedComment = data.rejectedComment;
    if( data.rejectedCommentAt ) this.rejectedCommentAt = data.rejectedCommentAt;
    if( data.rejectedAdminComment ) this.rejectedAdminComment = data.rejectedAdminComment;
    if( data.rejectedAdminCommentAt ) this.rejectedAdminCommentAt = data.rejectedAdminCommentAt;

    if( data.credentials ) {
      this.editable = data.credentials.editable;
      this.deletable = data.credentials.deletable;
      this.validable = false;
      this.validableSci = data.credentials.validableSci;
      this.validableAdm = data.credentials.validableAdm;
      this.sendable= data.credentials.sendable;
    }
  }

  static first( events ){
    var first = null;
    events.forEach((e1) => {
      if( first == null ){
        first = e1;
      } else {
        if( e1.isBefore(first) ){
          first = e1;
        }
      }
    });
    return first;
  }


  static sortByStart( events ){
    var sorted = events.sort((e1,e2) => {
      if( e1.mmStart < e2.mmStart )
          return -1

      else if( e1.mmStart > e2.mmStart )
          return 1

      return 0;
    });
    return sorted;
  }
}

EventDT.UID = 1
