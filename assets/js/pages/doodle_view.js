import {Utils} from "./../utils";

export class DoodleView {
    generateForm(e){
        Utils.showLoadingOverlay();
        e.preventDefault();
        let button = e.currentTarget;
        let id = button.getAttribute('data-id');
        let forId = button.getAttribute('data-for');
        let forDiv = document.getElementById(forId);
        let commentDiv = button.parentNode;

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

Utils.ready(function() {
    let doodleView = new DoodleView();

    let doodleComments = document.getElementsByClassName("doodle-comment-reply");

    for (let i = 0; i < doodleComments.length; i++) {
        doodleComments[i].addEventListener('click', e => {
            doodleView.generateForm(e);
        } );
    }
});