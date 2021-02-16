import 'bootstrap/js/src/modal.js';
import {Utils} from '../Utils';
import {TempFileManager} from '../TempFilesManager';

Utils.ready(function () {
    const tempFileManager = new TempFileManager();
    tempFileManager.bindUIEvents();
});
