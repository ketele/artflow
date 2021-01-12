import 'bootstrap/js/src/modal.js';
import {Utils} from "./../utils";

export class Gallery {
    changDoodleStatusShowModal(e){
        Utils.showLoadingOverlay();
        const xhr = new XMLHttpRequest();
        let obj = e.currentTarget;
        let button = e.relatedTarget;
        let id = button.dataset.id;

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let change_doodle_status_modal_body = document.getElementById('change-doodle-status-modal-body');

                if( response.status === true ){
                    change_doodle_status_modal_body.innerHTML = response.content;
                } else {
                }

                Utils.hideLoadingOverlay();
            }else{
            }
        };
        xhr.open("GET", "/admin/status_change_modal_view?id=" + id , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(null);
    }

    changDoodleStatus(e){
        const xhr = new XMLHttpRequest();
        let button = e.currentTarget;
        let formElement = document.getElementById('change-doodle-status-modal-form');
        let formData = new FormData(formElement);

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let error_element = document.getElementById('change-doodle-status-modal-error');

                if( response.status === true ){
                    error_element.classList.add('d-none');
                    window.location.reload(true);
                } else {
                    error_element.innerHTML = "Something went wrong";
                    error_element.classList.remove('d-none');
                }

                Utils.hideLoadingOverlay();
            }else{
            }
        };

        xhr.open("POST", "/admin/status_change_ajax" , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    }
}

Utils.ready(function() {
    let gallery = new Gallery();

    let changeDoodleStatusModal = document.getElementById('change-doodle-status-modal');

    changeDoodleStatusModal.addEventListener('show.bs.modal', e => {
        gallery.changDoodleStatusShowModal(e);
    });

    document.getElementById('change-doodle-status-modal-submit').addEventListener('click', e => {
        gallery.changDoodleStatus(e);
    } );

});