import 'bootstrap/js/src/modal.js';
import {Utils} from './../utils';

export class GalleryManager {
    bindUIEvents() {
        const changeDoodleStatusModal = document.getElementById('change-doodle-status-modal');

        changeDoodleStatusModal.addEventListener('show.bs.modal', e => {
            this.changDoodleStatusShowModal(e);
        });

        document.getElementById('change-doodle-status-modal-submit').addEventListener('click', e => {
            this.changDoodleStatus(e);
        });
    }

    changDoodleStatusShowModal(e) {
        Utils.showLoadingOverlay();
        const button = e.relatedTarget;
        const id = button.dataset.id;
        const manageTaskModalBody = document.getElementById('change-doodle-status-modal-body');

        fetch(`/api/doodle/status/${id}/edit`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manageTaskModalBody.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    changDoodleStatus(e) {
        const formElement = document.getElementById('change-doodle-status-modal-form');
        const formData = new FormData(formElement);
        const errorElement = document.getElementById('change-doodle-status-modal-error');
        let fetchApi;

        if (formData.get('id')) {
            fetchApi = fetch(`/api/doodle/status/${formData.get('id')}?${new URLSearchParams(formData).toString()}`, {method: 'PUT'});
        } else {
            fetchApi = fetch('/api/doodle/status', {
                method: 'POST',
                body: formData
            });
        }

        fetchApi.then(response => response.json().then(data => {
            if (response.status < 300) {
                errorElement.classList.add('d-none');
                window.location.reload(true);
            } else {
                if (data.error && data.error.length > 0) {
                    errorElement.innerHTML = Utils.generateListHTML(data.error);
                } else {
                    errorElement.innerHTML = 'Error: ' + response.statusText + ' ' + response.status;
                }
                errorElement.classList.remove('d-none');
            }
        }));
    }
}
