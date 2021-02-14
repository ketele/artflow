import 'bootstrap/js/src/modal.js';
import {Utils} from './utils';

export class TempFileManager {
    bindUIEvents() {
        const tempFileSubmits = document.getElementsByClassName('js-delete-temp-files-form');

        for (let i = 0; i < tempFileSubmits.length; i++) {
            tempFileSubmits[i].addEventListener('submit', e => {
                e.preventDefault();
                this.deleteTempFiles(e);
            });
        }
    }

    deleteTempFiles(e) {
        Utils.showLoadingOverlay();
        const data = new FormData(e.target);
        const year = data.get('year');
        const month = data.get('month');
        const lastDayInMonth = new Date(year, month, 0);
        const dateGte = `${year}-${month}-01`;
        const dateLte = lastDayInMonth.toISOString();

        fetch(`/api/tempfiles?action=delete&date[gte]=${dateGte}&date[lte]=${dateLte}`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    window.location.reload();
                }

                Utils.hideLoadingOverlay();
            }));
    }
}
