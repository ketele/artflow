import {Utils} from '../Utils';
import {GalleryManager} from '../doodle/GalleryManager';

Utils.ready(function () {
    const galleryManager = new GalleryManager();
    galleryManager.bindUIEvents();
});
