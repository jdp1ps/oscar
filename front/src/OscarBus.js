const messages = [];

const OscarBus = {
    id: "BUS" + Math.random(),
    name: "!OscarBus!",
    messages: messages,
    message(msg, type="notice"){
        messages.push({
            key: (new Date()).getTime(),
            msg: msg,
            date: new Date(),
            type: type,
            read: false
        })
    },
    errors(){
        let err = [];

    },
    unreadErrors(){
        let err = [];
        for( let i =0; i<messages.length; i++ ){
            if( !messages[i].read ){
                err.push(messages[i]);
            }
        }
        return err;
    }
};

export default OscarBus;
