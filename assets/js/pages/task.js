import 'bootstrap/js/src/modal.js';
import {Utils} from "./../utils";

export class Task {
    changTaskStatusShowModal(e) {
        Utils.showLoadingOverlay();
        let button = e.relatedTarget;
        let id = button.dataset.id;
        let manage_task_modal_body = document.getElementById('change-task-status-modal-body');

        fetch(`/api/task/status/${id}/edit`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manage_task_modal_body.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    changeTaskStatus(e) {
        const xhr = new XMLHttpRequest();
        let button = e.currentTarget;
        let formElement = document.getElementById('change-task-status-modal-form');
        let formData = new FormData(formElement);
        let error_element = document.getElementById('change-task-status-modal-error');

        fetch("/api/task/" + formData.get('id') + "?" + new URLSearchParams(formData).toString(), {method: 'PATCH'})
            .then(response => response.json().then(data => {
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

    manageTaskShowModal(e) {
        Utils.showLoadingOverlay();
        let button = e.relatedTarget;
        let id = button.dataset.id;
        let manage_task_modal_body = document.getElementById('mange-task-modal-body');

        fetch(`/api/task${((id.length > 0) ? `/${id}` : ``)}/manage`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manage_task_modal_body.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    manageTask(e) {
        let formElement = document.getElementById('manage-task-modal-form');
        let formData = new FormData(formElement);
        let error_element = document.getElementById('manage-task-modal-error');
        let fetchApi;

        if (formData.get('id')) {
            fetchApi = fetch("/api/task/" + formData.get('id') + "?" + new URLSearchParams(formData).toString(), {method: 'PUT'});
        } else {
            fetchApi = fetch("/api/task", {
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

    manageTaskBoardShowModal(e) {
        Utils.showLoadingOverlay();
        let button = e.relatedTarget;
        let id = button.dataset.id;
        let manage_task_modal_body = document.getElementById('mange-task-board-modal-body');

        fetch(`/api/task/board${((id.length > 0) ? `/${id}` : ``)}/manage`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manage_task_modal_body.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    manageTaskBoard(e) {
        let formElement = document.getElementById('manage-task-board-modal-form');
        let formData = new FormData(formElement);
        let error_element = document.getElementById('manage-task-board-modal-error');
        let fetchApi;

        if (formData.get('id')) {
            fetchApi = fetch("/api/task/board/" + formData.get('id') + "?" + new URLSearchParams(formData).toString(), {method: 'PUT'});
        } else {
            fetchApi = fetch("/api/task/board", {
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

    deleteTaskShowModal(e) {
        Utils.showLoadingOverlay();
        let button = e.relatedTarget;
        let id = button.dataset.id;
        let manage_task_modal_body = document.getElementById('delete-task-modal-body');

        fetch("/api/task/" + id + '/delete', {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manage_task_modal_body.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    deleteTask(e) {
        let formElement = document.getElementById('delete-task-modal-form');
        let formData = new FormData(formElement);
        let error_element = document.getElementById('delete-task-modal-error');

        fetch("/api/task/" + formData.get('id'), {method: 'DELETE'})
            .then(response => response.json().then(data => {
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

    deleteTaskBoardShowModal(e) {
        Utils.showLoadingOverlay();
        let button = e.relatedTarget;
        let id = button.dataset.id;
        let manage_task_modal_body = document.getElementById('delete-task-board-modal-body');

        fetch("/api/task/board/" + id + '/delete', {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    manage_task_modal_body.innerHTML = data.content;
                }

                Utils.hideLoadingOverlay();
            }));
    }

    deleteTaskBoard(e) {
        let formElement = document.getElementById('delete-task-board-modal-form');
        let formData = new FormData(formElement);
        let error_element = document.getElementById('delete-task-board-modal-error');

        fetch("/api/task/board/" + formData.get('id'), {method: 'DELETE'})
            .then(response => response.json().then(data => {
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

Utils.ready(function () {
    let task = new Task();

    let changeTaskStatusModal = document.getElementById('change-task-status-modal');

    changeTaskStatusModal.addEventListener('show.bs.modal', e => {
        task.changTaskStatusShowModal(e);
    });

    document.getElementById('change-task-status-modal-submit').addEventListener('click', e => {
        task.changeTaskStatus(e);
    });

    let manageTaskModal = document.getElementById('mange-task-modal');

    manageTaskModal.addEventListener('show.bs.modal', e => {
        task.manageTaskShowModal(e);
    });

    document.getElementById('manage-task-modal-submit').addEventListener('click', e => {
        task.manageTask(e);
    });

    let manageTaskBoardModal = document.getElementById('mange-task-board-modal');

    manageTaskBoardModal.addEventListener('show.bs.modal', e => {
        task.manageTaskBoardShowModal(e);
    });

    document.getElementById('manage-task-board-modal-submit').addEventListener('click', e => {
        task.manageTaskBoard(e);
    });

    let deleteTaskModal = document.getElementById('delete-task-modal');

    deleteTaskModal.addEventListener('show.bs.modal', e => {
        task.deleteTaskShowModal(e);
    });

    document.getElementById('delete-task-modal-submit').addEventListener('click', e => {
        task.deleteTask(e);
    });

    let deleteTaskBoardModal = document.getElementById('delete-task-board-modal');

    deleteTaskBoardModal.addEventListener('show.bs.modal', e => {
        task.deleteTaskBoardShowModal(e);
    });

    document.getElementById('delete-task-board-modal-submit').addEventListener('click', e => {
        task.deleteTaskBoard(e);
    });

});