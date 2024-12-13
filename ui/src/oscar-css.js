//import 'bootstrap/dist/css/bootstrap.css';
import './oscar-css.scss';


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
/// MODE PRIVE
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
document.querySelector('#btn-toggle-private').addEventListener('click', e => {
    e.preventDefault();
    privatizer('switch');
});

function privatizer(setprivate = null) {
    let isPrivate = localStorage.getItem('privatize');

    if (setprivate !== null) {
        let actual = document.querySelector('body').classList.contains('privatize');
        let to = !actual;
        isPrivate = to ? "1" : "0";
        localStorage.setItem('privatize', isPrivate);
    }

    if (isPrivate === "1") {
        document.querySelector('body').classList.add('privatize');
    } else {
        document.querySelector('body').classList.remove('privatize');
    }
}

privatizer();