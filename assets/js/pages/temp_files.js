import 'bootstrap/js/src/modal.js';
import {Utils} from "../utils";
import {TempFileManager} from "../temp_files_manager";

Utils.ready(function() {
    const tempFileManager = new TempFileManager();
});