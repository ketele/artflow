import 'bootstrap/js/src/modal.js';
import {Utils} from "./../utils";

export class GalleryManager {
    constructor() {
        this.bindUIEvents();
    }

    bindUIEvents() {
        let changeDoodleStatusModal = document.getElementById('change-doodle-status-modal');

        changeDoodleStatusModal.addEventListener('show.bs.modal', e => {
            this.changDoodleStatusShowModal(e);
        });

        document.getElementById('change-doodle-status-modal-submit').addEventListener('click', e => {
            this.changDoodleStatus(e);
        } );
    }

    changDoodleStatusShowModal(e){
        Utils.showLoadingOverlay();
        let button = e.relatedTarget;
        let id = button.dataset.id;
        let manage_task_modal_body = document.getElementById('change-doodle-status-modal-body');

        fetch(`/api/doodle/status/${id}/edit`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manage_task_modal_body.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    changDoodleStatus(e){
        let formElement = document.getElementById('change-doodle-status-modal-form');
        let formData = new FormData(formElement);
        let error_element = document.getElementById('change-doodle-status-modal-error');
        let fetchApi;

        if (formData.get('id')) {
            fetchApi = fetch("/api/doodle/status/" + formData.get('id') + "?" + new URLSearchParams(formData).toString(), {method: 'PUT'});
        } else {
            fetchApi = fetch("/api/doodle/status", {
                method: 'POST',
                body: formData
            });
        }

        fetchApi.then(response => response.json().then(data => {
            if (response.status < 300) {
                error_element.classList.add('d-none');
                window.location.reload(true);
            } else {
                if (data.error && data.error.length > 0) {
                    error_element.innerHTML = Utils.generateListHTML(data.error);
                } else {
                    error_element.innerHTML = 'Error: ' + response.statusText + ' ' + response.status;
                }
                error_element.classList.remove('d-none');
            }
        }));
    }
}