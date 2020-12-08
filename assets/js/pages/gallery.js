import 'bootstrap/js/src/modal.js';
import {Utils} from "./../utils";

class Gallery {

}

Utils.ready(function() {
    let gallery = new Gallery();

    /*let changeDoodleStatusButton = document.getElementsByClassName("change-doodle-status");

    let i;
    for (i = 0; i < changeDoodleStatusButton.length; i++) {
        changeDoodleStatusButton[i].addEventListener('click', e => {
            let obj = e.currentTarget;
            let id = obj.dataset.id;
            console.log('Clicked ' + i + ', id:' + id);
            //gallery.deleteTempFiles(obj);
        } );
    }*/

    let changeDoodleStatusModal = document.getElementById('change-doodle-status-modal');

    changeDoodleStatusModal.addEventListener('show.bs.modal', e => {
        let obj = e.currentTarget;
        let button = e.relatedTarget;
        let id = button.dataset.id;
        console.log('Clicked, id:' + id);

        /*
        // Button that triggered the modal
        var button = event.relatedTarget
        // Extract info from data-bs-* attributes
        var recipient = button.getAttribute('data-bs-whatever')
        // If necessary, you could initiate an AJAX request here
        // and then do the updating in a callback.
        //
        // Update the modal's content.
        var modalTitle = changeDoodleStatusModal.querySelector('.modal-title')
        var modalBodyInput = changeDoodleStatusModal.querySelector('.modal-body input')

        modalTitle.textContent = 'New message to ' + recipient
        modalBodyInput.value = recipient/**/
    });

});