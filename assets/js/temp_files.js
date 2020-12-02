import 'bootstrap/js/src/modal.js';

class TempFile{
    deleteTempFiles(obj){
        const xhr = new XMLHttpRequest();
        let year = obj.dataset.year;
        let month = obj.dataset.month;

        xhr.onreadystatechange = e => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.response);
                let error_element = document.getElementById('delete-temp-files-modal-error');

                if( response.status === true ){
                    error_element.classList.add('d-none');
                    $('#delete-temp-files-modal').modal('hide');
                    window.location.reload(true);
                } else {
                    error_element.innerHTML = "Something went wrong";
                    error_element.classList.remove('d-none');
                }
            }else{
            }
        };
        xhr.open("GET", "/admin/temp_files_delete_ajax?year=" + year + "&month=" +month , false);
        xhr.send(null);
        /**/
    }
}

window.addEventListener('load', e => {
    let tempFile = new TempFile();

    document.getElementById('delete-temp-files').addEventListener('click', e => {
        tempFile.deleteTempFiles(e.currentTarget);
    } );
});