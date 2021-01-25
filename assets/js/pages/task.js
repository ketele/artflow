import 'bootstrap/js/src/modal.js';
import {Utils} from "./../utils";

export class Task {
    changTaskStatusShowModal(e){
        Utils.showLoadingOverlay();
        const xhr = new XMLHttpRequest();
        let obj = e.currentTarget;
        let button = e.relatedTarget;
        let id = button.dataset.id;

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let change_task_status_modal_body = document.getElementById('change-task-status-modal-body');

                if( response.status === true ){
                    change_task_status_modal_body.innerHTML = response.content;
                } else {
                }

                Utils.hideLoadingOverlay();
            }else{
            }
        };
        xhr.open("GET", "/task/status_change_modal_view?id=" + id , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(null);
    }

    changeTaskStatus(e){
        const xhr = new XMLHttpRequest();
        let button = e.currentTarget;
        let formElement = document.getElementById('change-task-status-modal-form');
        let formData = new FormData(formElement);

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let error_element = document.getElementById('change-task-status-modal-error');

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

        xhr.open("POST", "/task/status_change_ajax" , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    }

    manageTaskShowModal(e){
        Utils.showLoadingOverlay();
        const xhr = new XMLHttpRequest();
        let obj = e.currentTarget;
        let button = e.relatedTarget;
        let id = button.dataset.id;

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let manage_task_modal_body = document.getElementById('mange-task-modal-body');

                if( response.status === true ){
                    manage_task_modal_body.innerHTML = response.content;
                } else {
                }

                Utils.hideLoadingOverlay();
            }else{
            }
        };
        xhr.open("GET", "/task/manage_modal_view?id=" + id , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(null);
    }

    manageTask(e){
        const xhr = new XMLHttpRequest();
        let button = e.currentTarget;
        let formElement = document.getElementById('manage-task-modal-form');
        let formData = new FormData(formElement);

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let error_element = document.getElementById('manage-task-modal-error');

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

        xhr.open("POST", "/task/manage_ajax" , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    }

    manageTaskBoardShowModal(e){
        Utils.showLoadingOverlay();
        const xhr = new XMLHttpRequest();
        let obj = e.currentTarget;
        let button = e.relatedTarget;
        let id = button.dataset.id;

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let manage_task_modal_body = document.getElementById('mange-task-board-modal-body');

                if( response.status === true ){
                    manage_task_modal_body.innerHTML = response.content;
                } else {
                }

                Utils.hideLoadingOverlay();
            }else{
            }
        };
        xhr.open("GET", "/task/manage_board_modal_view?id=" + id , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(null);
    }

    manageTaskBoard(e){
        const xhr = new XMLHttpRequest();
        let button = e.currentTarget;
        let formElement = document.getElementById('manage-task-board-modal-form');
        let formData = new FormData(formElement);

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let error_element = document.getElementById('manage-task-board-modal-error');

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

        xhr.open("POST", "/task/manage_board_ajax" , false);
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.send(formData);
    }
}

Utils.ready(function() {
    let task = new Task();

    let changeTaskStatusModal = document.getElementById('change-task-status-modal');
    let manageTaskModal = document.getElementById('mange-task-modal');
    let manageTaskBoardModal = document.getElementById('mange-task-board-modal');

    changeTaskStatusModal.addEventListener('show.bs.modal', e => {
        task.changTaskStatusShowModal(e);
    });

    document.getElementById('change-task-status-modal-submit').addEventListener('click', e => {
        task.changeTaskStatus(e);
    } );

    manageTaskModal.addEventListener('show.bs.modal', e => {
        task.manageTaskShowModal(e);
    });

    document.getElementById('manage-task-modal-submit').addEventListener('click', e => {
        task.manageTask(e);
    } );

    manageTaskBoardModal.addEventListener('show.bs.modal', e => {
        task.manageTaskBoardShowModal(e);
    });

    document.getElementById('manage-task-board-modal-submit').addEventListener('click', e => {
        task.manageTaskBoard(e);
    } );

});