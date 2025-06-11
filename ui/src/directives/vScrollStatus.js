let handlerClick = function(e) {
  if( e.target.classList.contains("can-scroll--open") ) {
      e.target.classList.remove("can-scroll--open");
  } else {
      e.target.classList.add("can-scroll--open");
  }
};
const vScrollStatus = {
    updated(el) {
        const hasScroll = el.scrollHeight > el.clientHeight;
        if (hasScroll) {
            console.log("can scroll");
            el.classList.add('can-scroll');
            el.addEventListener('click', handlerClick);

        } else {
            console.log("can't scroll");
            el.classList.remove('can-scroll');
            el.removeEventListener('click', handlerClick);
        }
    }
};

export default vScrollStatus;