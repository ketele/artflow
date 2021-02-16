import {Utils} from './../Utils';

export class DoodleManager {
    bindUIEvents() {
        const doodleComments = document.getElementsByClassName('doodle-comment-reply');

        for (let i = 0; i < doodleComments.length; i++) {
            doodleComments[i].addEventListener('click', e => {
                this.generateForm(e);
            });
        }
    }

    generateForm(e) {
        Utils.showLoadingOverlay();
        e.preventDefault();
        const button = e.currentTarget;
        const id = button.getAttribute('data-id');
        const commentDiv = button.parentNode;

        fetch(`/api/doodle/comment/${id}/manage`, {method: 'GET'})
            .then(response => response.json().then(data => {
                if (response.status < 300) {
                    commentDiv.parentNode.insertBefore(Utils.createElementFromHTML(data.content), commentDiv.nextSibling);
                    button.classList.add('d-none');
                }

                Utils.hideLoadingOverlay();
            }));
    }
}
