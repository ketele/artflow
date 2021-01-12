import {Utils} from "./../utils";

export class DoodleView {
    generateForm(e){
        e.preventDefault();
        const xhr = new XMLHttpRequest();
        let button = e.currentTarget;
        let id = button.getAttribute('data-id');
        let forId = button.getAttribute('data-for');
        let forDiv = document.getElementById(forId);

        xhr.onreadystatechange = e => {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                let response = JSON.parse(xhr.response);

                if( response.status === true ){
                    forDiv.innerHTML += response.content;
                }
            }else{
            }
        };

        xhr.open("POST", "/doodle_comment_ajax" , true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.send('id=' + id);
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